@extends('emails.layout', ['subject' => $subject ?? 'Support Ticket Update - Rook Hosting'])

@section('content')
    <div style="margin-bottom: 20px;">
        <span style="display: inline-block; padding: 4px 10px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; border-radius: 4px; background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;">
            Ticket #{{ $ticket->ticket_number }}
        </span>
        <span style="display: inline-block; margin-left: 8px; font-size: 12px; color: #64748b; font-weight: 600;">
            Status: {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
        </span>
    </div>

    <h2 style="margin: 0 0 16px; font-size: 20px; font-weight: 700; color: #0f172a;">
        {{ $ticket->subject }}
    </h2>

    <p style="margin: 0 0 16px; font-size: 15px; color: #334155; line-height: 1.6;">
        {{ $bodyText }}
    </p>

    @if(!empty($latestMessage))
        <div style="margin: 20px 0; padding: 18px 20px; background-color: #f8fafc; border-left: 4px solid #10b981; border-radius: 6px; font-size: 14px; color: #1e293b; line-height: 1.6;">
            <div style="font-weight: 700; font-size: 12px; color: #64748b; margin-bottom: 6px; text-transform: uppercase;">
                {{ $senderName ?? 'Rook Support Team' }} wrote:
            </div>
            {!! nl2br(e($latestMessage)) !!}
        </div>
    @endif

    <div class="button-wrapper" style="text-align: center; margin: 30px 0;">
        <a href="{{ $ticketUrl }}" class="btn-primary" style="background-color: #10B981; color: #ffffff !important; padding: 14px 32px; border-radius: 8px; font-weight: 700; text-decoration: none; display: inline-block; font-size: 14px;">
            View Ticket &amp; Reply &rarr;
        </a>
    </div>

    <p style="margin: 24px 0 0; font-size: 12px; color: #94a3b8; word-break: break-all;">
        Direct Ticket Link: <a href="{{ $ticketUrl }}" style="color: #10B981;">{{ $ticketUrl }}</a>
    </p>
@endsection
