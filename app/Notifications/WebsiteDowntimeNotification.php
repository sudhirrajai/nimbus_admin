<?php

namespace App\Notifications;

use App\Models\HostingAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WebsiteDowntimeNotification extends Notification
{
    use Queueable;

    public HostingAccount $account;
    public ?int $statusCode;
    public ?string $errorMessage;
    public ?int $responseTimeMs;
    public string $checkedAt;

    public function __construct(
        HostingAccount $account,
        ?int $statusCode = null,
        ?string $errorMessage = null,
        ?int $responseTimeMs = null,
        ?string $checkedAt = null
    ) {
        $this->account = $account;
        $this->statusCode = $statusCode;
        $this->errorMessage = $errorMessage;
        $this->responseTimeMs = $responseTimeMs;
        $this->checkedAt = $checkedAt ?? now()->format('Y-m-d H:i:s T');
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $domain = $this->account->domain;
        $codeStr = $this->statusCode ? "HTTP {$this->statusCode}" : "Connection/DNS Failure";
        $subject = "🚨 DOWNTIME ALERT: {$domain} is DOWN ({$codeStr}) - Nimbus Monitor";

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.downtime-alert', [
                'admin' => $notifiable,
                'account' => $this->account,
                'domain' => $domain,
                'statusCode' => $this->statusCode,
                'errorMessage' => $this->errorMessage,
                'responseTimeMs' => $this->responseTimeMs,
                'checkedAt' => $this->checkedAt,
                'clientName' => $this->account->user?->name ?? 'Client',
                'clientEmail' => $this->account->user?->email ?? 'N/A',
                'serverName' => $this->account->server?->name ?? 'Unassigned Node',
                'serverIp' => $this->account->server?->ip_address ?? 'N/A',
                'adminUrl' => route('admin.hosting.index'),
            ]);
    }
}
