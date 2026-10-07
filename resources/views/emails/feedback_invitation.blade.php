@extends('emails.layout', ['subject' => $subject ?? 'We value your feedback - Roook Hosting'])

@section('content')
    <h2 style="margin: 0 0 16px; font-size: 20px; font-weight: 700; color: #0f172a;">
        Hi {{ $invitation->recipient_name ?: 'there' }},
    </h2>

    <p style="margin: 0 0 16px; font-size: 15px; color: #334155; line-height: 1.6;">
        We'd love to hear about your experience with <strong>{{ $form->title }}</strong>. Your review and insights help us continuously elevate our services, infrastructure speed, and support quality.
    </p>

    @if(!empty($personalNote))
        <div style="margin: 0 0 20px; padding: 14px 18px; background-color: #f1f5f9; border-left: 4px solid #10b981; border-radius: 4px; font-size: 14px; color: #334155; font-style: italic;">
            "{{ $personalNote }}"
        </div>
    @endif

    <p style="margin: 0 0 24px; font-size: 14px; color: #64748b;">
        It takes less than 60 seconds to complete. Click the button below to share your thoughts, rating, and suggestions:
    </p>

    <div class="button-wrapper" style="text-align: center; margin: 30px 0;">
        <a href="{{ $inviteUrl }}" class="btn-primary" style="background-color: #10B981; color: #ffffff !important; padding: 14px 32px; border-radius: 8px; font-weight: 700; text-decoration: none; display: inline-block; font-size: 14px;">
            Share Your Feedback &rarr;
        </a>
    </div>

    <p style="margin: 24px 0 0; font-size: 12px; color: #94a3b8; word-break: break-all;">
        If the button above doesn't work, you can copy and paste this link into your browser:<br/>
        <a href="{{ $inviteUrl }}" style="color: #10B981;">{{ $inviteUrl }}</a>
    </p>
@endsection
