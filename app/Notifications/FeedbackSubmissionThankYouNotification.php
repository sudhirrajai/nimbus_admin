<?php

namespace App\Notifications;

use App\Models\FeedbackForm;
use App\Models\FeedbackSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FeedbackSubmissionThankYouNotification extends Notification
{
    use Queueable;

    public FeedbackForm $form;
    public FeedbackSubmission $submission;

    public function __construct(FeedbackForm $form, FeedbackSubmission $submission)
    {
        $this->form = $form;
        $this->submission = $submission;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $subject = "Thank you for your feedback: " . $this->form->title . " - Nimbus by VMCore";

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.feedback_thank_you', [
                'form' => $this->form,
                'submission' => $this->submission,
                'subject' => $subject,
            ]);
    }
}
