<?php

namespace App\Notifications;

use App\Models\HostingAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RenewalReminderNotification extends Notification
{
    use Queueable;

    public HostingAccount $account;
    public ?string $dueDate;
    public ?float $amount;

    public function __construct(HostingAccount $account, ?string $dueDate = null, ?float $amount = null)
    {
        $this->account = $account;
        $this->dueDate = $dueDate;
        $this->amount = $amount;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $domain = $this->account->domain;
        $subject = "Service Renewal Notice: {$domain} - Nimbus Cloud";

        $formattedAmount = '₹' . number_format($this->amount ?? $this->account->renewal_price ?? $this->account->initial_price ?? 0, 2);
        $formattedDate = $this->dueDate ?? ($this->account->renews_at ? $this->account->renews_at->format('M d, Y') : 'Upcoming Term');

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.reminder', [
                'user' => $notifiable,
                'serviceName' => $domain,
                'planName' => $this->account->plan_name,
                'renewalDate' => $formattedDate,
                'amount' => $formattedAmount,
                'actionUrl' => route('subscription'),
            ]);
    }
}
