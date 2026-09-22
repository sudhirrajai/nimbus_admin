<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceNotification extends Notification
{
    use Queueable;

    public Invoice $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $statusText = $this->invoice->status === 'paid' ? 'Payment Receipt' : 'Invoice';
        $subject = "{$statusText} #{$this->invoice->invoice_number} - Nimbus by VMCore";

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.invoice', [
                'invoice' => $this->invoice,
                'user' => $notifiable,
            ]);
    }
}
