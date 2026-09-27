<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketNotification extends Notification
{
    use Queueable;

    public Ticket $ticket;
    public string $type; // 'created', 'reply', 'resolved', 'closed'
    public ?string $latestMessage;
    public ?string $senderName;

    public function __construct(Ticket $ticket, string $type = 'reply', ?string $latestMessage = null, ?string $senderName = null)
    {
        $this->ticket = $ticket;
        $this->type = $type;
        $this->latestMessage = $latestMessage;
        $this->senderName = $senderName;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $ticketUrl = route('tickets.show', ['ticket' => $this->ticket->id]);

        switch ($this->type) {
            case 'created':
                $subject = "[#{$this->ticket->ticket_number}] Support Ticket Created: {$this->ticket->subject}";
                $bodyText = "Your support request has been received by the Nimbus Engineering team. An engineer will review your issue and reply promptly.";
                break;
            case 'resolved':
                $subject = "[#{$this->ticket->ticket_number}] Ticket Resolved: {$this->ticket->subject}";
                $bodyText = "Your support ticket has been marked as resolved by our technical team. If you need any further assistance, feel free to reopen or reply.";
                break;
            case 'closed':
                $subject = "[#{$this->ticket->ticket_number}] Ticket Closed: {$this->ticket->subject}";
                $bodyText = "Your support ticket #{$this->ticket->ticket_number} has been closed.";
                break;
            default: // reply
                $subject = "[#{$this->ticket->ticket_number}] New Reply on Ticket: {$this->ticket->subject}";
                $bodyText = "A new response has been posted to your support ticket:";
                break;
        }

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.ticket_notification', [
                'ticket' => $this->ticket,
                'subject' => $subject,
                'bodyText' => $bodyText,
                'latestMessage' => $this->latestMessage,
                'senderName' => $this->senderName,
                'ticketUrl' => $ticketUrl,
            ]);
    }
}
