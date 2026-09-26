<?php

namespace App\Services;

use App\Models\HostingAccount;
use App\Models\HostingAccountUptimeLog;
use App\Models\User;
use App\Notifications\WebsiteDowntimeNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Throwable;

class UptimeMonitorService
{
    /**
     * Check uptime for a single hosting account with public DNS & external reachability verification.
     */
    public function checkAccount(HostingAccount $account, ?string $adminEmail = null): array
    {
        $domain = trim($account->domain ?? $account->primary_domain ?? '');
        if (empty($domain)) {
            return [
                'status' => 'unknown',
                'status_code' => null,
                'response_time_ms' => null,
                'error' => 'No domain configured for this account.',
            ];
        }

        $cleanDomain = preg_replace('#^https?://#i', '', rtrim($domain, '/'));
        $cleanDomain = explode('/', $cleanDomain)[0]; // Just the host part

        $status = 'down';
        $statusCode = null;
        $responseTimeMs = null;
        $errorMessage = null;

        // Step 1: Local & Public DNS Verification
        $localIp = @gethostbyname($cleanDomain);

        // Check if local DNS failed or resolves to loopback / sinkhole (e.g. 127.0.0.1)
        if ($localIp === '127.0.0.1' || $localIp === '0.0.0.0' || str_starts_with($localIp, '127.') || $localIp === '::1') {
            $status = 'down';
            $errorMessage = "DNS resolves to loopback/sinkhole IP ({$localIp}). Domain is sinkholed by ISP or misconfigured in DNS.";
        } elseif ($localIp === $cleanDomain) {
            $status = 'down';
            $errorMessage = "DNS A record failed to resolve. Domain '{$cleanDomain}' is unresolvable or DNS records have changed.";
        }

        // Query Public DNS (Google DNS over HTTPS) to verify public internet propagation
        if ($errorMessage === null) {
            try {
                $dohResponse = Http::timeout(4)->get("https://dns.google/resolve", [
                    'name' => $cleanDomain,
                    'type' => 'A',
                ]);
                if ($dohResponse->successful()) {
                    $answers = $dohResponse->json('Answer') ?? [];
                    $publicIps = array_filter(array_map(fn($a) => $a['data'] ?? null, $answers));

                    if (empty($publicIps)) {
                        $status = 'down';
                        $errorMessage = "Public DNS (Google 8.8.8.8) could not find active A records for '{$cleanDomain}'.";
                    } else {
                        foreach ($publicIps as $pip) {
                            if ($pip === '127.0.0.1' || $pip === '0.0.0.0' || str_starts_with($pip, '127.')) {
                                $status = 'down';
                                $errorMessage = "Public DNS returned loopback/sinkhole IP ({$pip}). Domain is sinkholed or invalid.";
                                break;
                            }
                        }
                    }
                }
            } catch (Throwable $dohEx) {
                // Non-fatal if Google DoH is temporarily unreachable
            }
        }

        // Step 2: HTTP / HTTPS Ping (Only if DNS check passed)
        if ($errorMessage === null) {
            $targetUrl = "https://{$cleanDomain}";
            $start = microtime(true);

            try {
                // Verify SSL so invalid/mismatched certs (which block browsers) are detected as DOWN
                $response = Http::withHeaders([
                    'User-Agent' => 'Nimbus-Uptime-Monitor/1.0 (+https://nimbus-host.vmcore.in)',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                ])
                ->timeout(10)
                ->connectTimeout(6)
                ->get($targetUrl);

                $latency = (int) round((microtime(true) - $start) * 1000);
                $statusCode = $response->status();
                $responseTimeMs = $latency;
                $body = $response->body();

                // Check if response is the Nimbus Admin Panel default vhost rather than the client's site
                $isDefaultNimbus = (str_contains($body, 'Nimbus by VMCore') || str_contains($body, 'nimbus-host.vmcore.in')) && !str_contains($cleanDomain, 'vmcore.in');

                if ($isDefaultNimbus) {
                    $status = 'down';
                    $errorMessage = "Misconfigured VHost: Domain returned Nimbus Admin default panel instead of client website.";
                } elseif ($statusCode === 200) {
                    $status = 'up';
                    $errorMessage = null;
                } else {
                    $status = 'down';
                    $reason = $response->reason() ?: 'Non-200 Response';
                    $errorMessage = "HTTP {$statusCode} {$reason}";
                }
            } catch (Throwable $e) {
                $rawMsg = $e->getMessage();
                $latency = (int) round((microtime(true) - $start) * 1000);
                $responseTimeMs = $latency;
                $status = 'down';
                $statusCode = null;

                if (str_contains($rawMsg, 'SSL') || str_contains($rawMsg, 'certificate') || str_contains($rawMsg, 'CERT') || str_contains($rawMsg, 'WRONG_PRINCIPAL')) {
                    $errorMessage = "SSL Certificate Invalid: Certificate does not match domain or is expired. Browsers block access.";
                } elseif (str_contains($rawMsg, 'timed out') || str_contains($rawMsg, 'Operation timed out')) {
                    $errorMessage = "Connection timed out after 10s. Server is unresponsive or port 443 is blocked.";
                } elseif (str_contains($rawMsg, 'Could not resolve host') || str_contains($rawMsg, 'Name or service not known')) {
                    $errorMessage = "DNS lookup failed: Host '{$cleanDomain}' could not be resolved.";
                } elseif (str_contains($rawMsg, 'Connection refused')) {
                    $errorMessage = "Connection refused on ports 80/443. Web server may be offline or firewall blocking traffic.";
                } else {
                    // Try HTTP fallback to see if port 80 is online
                    try {
                        $httpUrl = "http://{$cleanDomain}";
                        $httpStart = microtime(true);
                        $httpResponse = Http::withHeaders([
                            'User-Agent' => 'Nimbus-Uptime-Monitor/1.0 (+https://nimbus-host.vmcore.in)',
                        ])
                        ->timeout(8)
                        ->connectTimeout(5)
                        ->get($httpUrl);

                        $httpLatency = (int) round((microtime(true) - $httpStart) * 1000);
                        $statusCode = $httpResponse->status();
                        $responseTimeMs = $httpLatency;
                        $httpBody = $httpResponse->body();

                        $isDefaultNimbusHttp = (str_contains($httpBody, 'Nimbus by VMCore') || str_contains($httpBody, 'nimbus-host.vmcore.in')) && !str_contains($cleanDomain, 'vmcore.in');

                        if ($isDefaultNimbusHttp) {
                            $status = 'down';
                            $errorMessage = "Misconfigured VHost: Domain returned Nimbus Admin default panel instead of client website.";
                        } elseif ($statusCode === 200) {
                            $status = 'up';
                            $errorMessage = null; // Site is UP over HTTP
                        } else {
                            $status = 'down';
                            $errorMessage = "HTTP {$statusCode} {$httpResponse->reason()}";
                        }
                    } catch (Throwable $httpEx) {
                        $errorMessage = Str::limit($rawMsg, 250);
                    }
                }
            }
        }

        $previousStatus = $account->uptime_status;

        // Step 3: Update Account
        $account->update([
            'uptime_status' => $status,
            'uptime_status_code' => $statusCode,
            'uptime_response_time_ms' => $responseTimeMs,
            'uptime_last_checked_at' => now(),
            'uptime_last_error' => $errorMessage,
        ]);

        // Step 4: Write Uptime Log
        try {
            HostingAccountUptimeLog::create([
                'hosting_account_id' => $account->id,
                'status' => $status,
                'status_code' => $statusCode,
                'response_time_ms' => $responseTimeMs,
                'error_message' => $errorMessage,
                'created_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::warning("Failed to write uptime log for account {$account->id}: " . $e->getMessage());
        }

        // Step 5: Send Email Alert for Red (Down) Websites
        if ($status === 'down') {
            $this->maybeSendDowntimeAlert($account, $statusCode, $errorMessage, $responseTimeMs, $previousStatus, $adminEmail);
        }

        return [
            'status' => $status,
            'status_code' => $statusCode,
            'response_time_ms' => $responseTimeMs,
            'error' => $errorMessage,
        ];
    }

    /**
     * Check all active hosting accounts.
     */
    public function checkAll(?string $adminEmail = null): array
    {
        $accounts = HostingAccount::where('status', 'active')
            ->whereNotNull('primary_domain')
            ->get();

        $results = [
            'total' => $accounts->count(),
            'up' => 0,
            'down' => 0,
            'checked_at' => now()->toIso8601String(),
        ];

        foreach ($accounts as $account) {
            $res = $this->checkAccount($account, $adminEmail);
            if ($res['status'] === 'up') {
                $results['up']++;
            } else {
                $results['down']++;
            }
        }

        return $results;
    }

    /**
     * Conditionally send downtime alert email to avoid flooding while ensuring admin is alerted.
     */
    protected function maybeSendDowntimeAlert(
        HostingAccount $account,
        ?int $statusCode,
        ?string $errorMessage,
        ?int $responseTimeMs,
        ?string $previousStatus,
        ?string $adminEmail = null
    ): void {
        $lastAlert = $account->uptime_last_alert_at;

        // Send alert if:
        // 1. Never alerted before
        // 2. State transition: was previously UP (or unknown) and is now DOWN
        // 3. Or it has been at least 60 minutes since last alert
        $shouldSend = false;
        if (!$lastAlert) {
            $shouldSend = true;
        } elseif ($previousStatus === 'up') {
            $shouldSend = true;
        } elseif ($lastAlert->diffInMinutes(now()) >= 60) {
            $shouldSend = true;
        }

        if (!$shouldSend) {
            return;
        }

        $notification = new WebsiteDowntimeNotification(
            $account,
            $statusCode,
            $errorMessage,
            $responseTimeMs,
            now()->format('d M Y, h:i A T')
        );

        try {
            if (!empty($adminEmail)) {
                // If a specific admin is logged in and triggering the check, send to that admin
                $adminUser = User::where('email', $adminEmail)->first();
                if ($adminUser) {
                    $adminUser->notify($notification);
                } else {
                    Notification::route('mail', $adminEmail)->notify($notification);
                }
            } else {
                // Automated background check: send to all active admins
                $admins = User::where('is_admin', true)->where('is_active', true)->get();
                if ($admins->isNotEmpty()) {
                    Notification::send($admins, $notification);
                }
            }

            $account->update(['uptime_last_alert_at' => now()]);
        } catch (Throwable $e) {
            Log::error("Failed to send downtime alert email for domain {$account->domain}: " . $e->getMessage());
        }
    }
}
