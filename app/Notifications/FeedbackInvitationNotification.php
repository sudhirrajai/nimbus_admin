<?php

namespace App\Notifications;

use App\Models\FeedbackForm;
use App\Models\FeedbackInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FeedbackInvitationNotification extends Notification
{
    use Queueable;

    public FeedbackForm $form;
    public FeedbackInvitation $invitation;
    public ?string $personalNote;

    public function __construct(FeedbackForm $form, FeedbackInvitation $invitation, ?string $personalNote = null)
    {
        $this->form = $form;
        $this->invitation = $invitation;
        $this->personalNote = $personalNote;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $subject = "We'd love your feedback: " . $this->form->title . " - Nimbus by VMCore";

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.feedback_invitation', [
                'form' => $this->form,
                'invitation' => $this->invitation,
                'personalNote' => $this->personalNote,
                'inviteUrl' => $this->invitation->invite_url,
                'subject' => $subject,
            ]);
    }
}
