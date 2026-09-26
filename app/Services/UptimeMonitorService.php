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

        // Step 1: Query Authoritative Public DNS (Cloudflare DoH + Google DoH)
        $publicIps = [];

        // Try Cloudflare DNS over HTTPS first
        try {
            $cfResponse = Http::withHeaders([
                'Accept' => 'application/dns-json'
            ])->timeout(4)->get("https://cloudflare-dns.com/dns-query", [
                'name' => $cleanDomain,
                'type' => 'A',
            ]);

            if ($cfResponse->successful()) {
                $answers = $cfResponse->json('Answer') ?? [];
                $publicIps = array_filter(array_map(fn($a) => $a['data'] ?? null, $answers));
            }
        } catch (Throwable $e) {
            // fallback
        }

        // Try Google DNS over HTTPS if Cloudflare returned empty
        if (empty($publicIps)) {
            try {
                $gResponse = Http::timeout(4)->get("https://dns.google/resolve", [
                    'name' => $cleanDomain,
                    'type' => 'A',
                ]);

                if ($gResponse->successful()) {
                    $answers = $gResponse->json('Answer') ?? [];
                    $publicIps = array_filter(array_map(fn($a) => $a['data'] ?? null, $answers));
                }
            } catch (Throwable $e) {
                // fallback
            }
        }

        // Fallback to local system resolver if both DoH queries failed
        if (empty($publicIps)) {
            $localIp = @gethostbyname($cleanDomain);
            if ($localIp !== $cleanDomain) {
                $publicIps = [$localIp];
            }
        }

        // Check if no IP was found at all
        if (empty($publicIps)) {
            $status = 'down';
            $errorMessage = "Public DNS lookup failed. Domain '{$cleanDomain}' has no active A records or nameservers are propagating.";
        } else {
            // Check for loopback / sinkhole IP (e.g. 127.0.0.1 or 0.0.0.0)
            foreach ($publicIps as $ip) {
                if ($ip === '127.0.0.1' || $ip === '0.0.0.0' || str_starts_with($ip, '127.') || $ip === '::1') {
                    $status = 'down';
                    $errorMessage = "DNS resolves to loopback/sinkhole IP ({$ip}). Domain is blocked by ISP or unconfigured in DNS.";
                    break;
                }
            }
        }

        // Step 2: HTTP / HTTPS Ping resolved directly to the Public IP
        if ($errorMessage === null && !empty($publicIps)) {
            $targetIp = $publicIps[0];
            $targetUrl = "https://{$cleanDomain}";
            $start = microtime(true);

            // Execute cURL with CURLOPT_RESOLVE to test the exact public IP real visitors hit
            $ch = curl_init($targetUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 6);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Nimbus-Uptime-Monitor/1.0 (+https://nimbus-host.vmcore.in)');
            curl_setopt($ch, CURLOPT_RESOLVE, [
                "{$cleanDomain}:443:{$targetIp}",
                "{$cleanDomain}:80:{$targetIp}",
            ]);

            $body = curl_exec($ch);
            $curlError = curl_error($ch);
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $latency = (int) round((microtime(true) - $start) * 1000);
            $responseTimeMs = $latency;
            curl_close($ch);

            if ($curlError) {
                $status = 'down';
                $statusCode = null;

                if (str_contains($curlError, 'SSL') || str_contains($curlError, 'handshake failure') || str_contains($curlError, 'certificate')) {
                    $errorMessage = "TLS/SSL Handshake Failure on public IP ({$targetIp}): Edge SSL certificate is pending or invalid. Browsers cannot open the site.";
                } elseif (str_contains($curlError, 'timed out') || str_contains($curlError, 'Operation timed out')) {
                    $errorMessage = "Connection to public IP ({$targetIp}) timed out after 10s. Server or firewall is blocking incoming traffic.";
                } elseif (str_contains($curlError, 'Connection refused')) {
                    $errorMessage = "Connection to public IP ({$targetIp}) refused on port 443.";
                } else {
                    $errorMessage = Str::limit($curlError, 250);
                }
            } else {
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
                    $errorMessage = "HTTP {$statusCode} response received from public IP ({$targetIp}).";
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
