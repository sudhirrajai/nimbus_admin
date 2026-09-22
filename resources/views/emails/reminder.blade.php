@extends('emails.layout')

@section('content')
<div style="margin-bottom: 24px;">
    <span style="display: inline-block; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; padding: 4px 10px; border-radius: 9999px; background-color: #fef3c7; color: #b45309;">
        Service Renewal Notice
    </span>
    <h1 style="font-size: 22px; font-weight: 800; color: #0f172a; margin: 12px 0 4px 0; letter-spacing: -0.02em;">
        Upcoming Subscription Renewal
    </h1>
    <p style="font-size: 12px; color: #64748b; margin: 0;">
        Action recommended to prevent service interruption
    </p>
</div>

<p style="font-size: 14px; color: #334155; margin: 0 0 16px 0;">
    Hello {{ $user->name ?? 'Valued Client' }},
</p>

<p style="font-size: 14px; color: #334155; margin: 0 0 24px 0;">
    This is a friendly reminder that your cloud hosting service for <strong>{{ $serviceName ?? 'your server' }}</strong> is scheduled for renewal on <strong>{{ $renewalDate ?? 'upcoming renewal period' }}</strong>.
</p>

<!-- Service Details Box -->
<div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 24px;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="font-size: 13px; color: #1e293b;">
        <tr>
            <td style="padding: 6px 0; color: #64748b; font-weight: 600;">Service / Domain:</td>
            <td align="right" style="padding: 6px 0; font-weight: 700; color: #0f172a; font-family: monospace;">{{ $serviceName ?? 'Cloud Instance' }}</td>
        </tr>
        <tr>
            <td style="padding: 6px 0; color: #64748b; font-weight: 600;">Plan Tier:</td>
            <td align="right" style="padding: 6px 0; font-weight: 700; color: #0f172a;">{{ $planName ?? 'Managed Cloud VPS' }}</td>
        </tr>
        <tr>
            <td style="padding: 6px 0; color: #64748b; font-weight: 600;">Renewal Date:</td>
            <td align="right" style="padding: 6px 0; font-weight: 700; color: #b45309;">{{ $renewalDate ?? 'N/A' }}</td>
        </tr>
        <tr style="border-top: 1px solid #e2e8f0;">
            <td style="padding: 12px 0 0 0; color: #0f172a; font-weight: 700; font-size: 14px;">Renewal Amount:</td>
            <td align="right" style="padding: 12px 0 0 0; font-weight: 800; font-size: 16px; font-family: monospace; color: #10B981;">
                {{ $amount ?? '₹0.00' }}
            </td>
        </tr>
    </table>
</div>

<p style="font-size: 13px; color: #475569; margin: 0 0 24px 0;">
    Your automated server backups, SSL automation, and 24/7 infrastructure monitoring will continue seamlessly upon renewal.
</p>

<div class="button-wrapper">
    <a href="{{ $actionUrl ?? route('subscription') }}" class="btn-primary" target="_blank">
        Review &amp; Manage Subscription
    </a>
</div>
@endsection
