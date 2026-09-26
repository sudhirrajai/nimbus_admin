@extends('emails.layout', ['subject' => "🚨 Downtime Alert: {$domain} is DOWN"])

@section('content')
<div style="margin-bottom: 24px;">
    <!-- Alert Badge -->
    <div style="display: inline-block; background-color: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 6px 14px; border-radius: 9999px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px;">
        ● Downtime Detected
    </div>

    <h1 style="margin: 0 0 6px 0; font-size: 22px; font-weight: 800; color: #0f172a; letter-spacing: -0.02em;">
        Website Service Interruption
    </h1>
    <p style="margin: 0; font-size: 14px; color: #64748b;">
        Nimbus Automated Uptime Monitor detected that client domain <strong style="color: #0f172a; font-family: monospace;">{{ $domain }}</strong> is currently unreachable or returned a non-200 status code.
    </p>
</div>

<!-- Diagnostics Box -->
<div style="background-color: #fff1f2; border: 1px solid #ffe4e6; border-left: 4px solid #e11d48; border-radius: 8px; padding: 18px 20px; margin-bottom: 24px;">
    <div style="font-size: 12px; font-weight: 700; color: #9f1239; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">
        Monitor Diagnostic Summary
    </div>
    <div style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 13px; color: #881337; line-height: 1.5; word-break: break-word;">
        {{ $errorMessage ?? 'Website did not respond with HTTP 200 OK. Connection refused, timed out, or DNS A record error.' }}
    </div>
</div>

<!-- Details Table -->
<table style="width: 100%; border-collapse: collapse; margin-bottom: 24px; font-size: 13px; border: 1px solid #f1f5f9; border-radius: 8px; overflow: hidden;">
    <tr style="background-color: #f8fafc; border-bottom: 1px solid #f1f5f9;">
        <td style="padding: 10px 16px; font-weight: 600; color: #475569; width: 35%;">Domain Name</td>
        <td style="padding: 10px 16px; font-weight: 700; color: #0f172a; font-family: monospace;">
            <a href="https://{{ $domain }}" target="_blank" style="color: #2563eb; text-decoration: underline;">{{ $domain }}</a>
        </td>
    </tr>
    <tr style="border-bottom: 1px solid #f1f5f9;">
        <td style="padding: 10px 16px; font-weight: 600; color: #475569;">HTTP Response Code</td>
        <td style="padding: 10px 16px; font-weight: 700;">
            @if($statusCode)
                <span style="color: #dc2626;">HTTP {{ $statusCode }}</span>
            @else
                <span style="color: #dc2626;">No Response (DNS / Network Timeout)</span>
            @endif
        </td>
    </tr>
    <tr style="background-color: #f8fafc; border-bottom: 1px solid #f1f5f9;">
        <td style="padding: 10px 16px; font-weight: 600; color: #475569;">Assigned Client</td>
        <td style="padding: 10px 16px; color: #1e293b;">
            <strong>{{ $clientName }}</strong> &lt;{{ $clientEmail }}&gt;
        </td>
    </tr>
    <tr style="border-bottom: 1px solid #f1f5f9;">
        <td style="padding: 10px 16px; font-weight: 600; color: #475569;">Hosting Node / Server</td>
        <td style="padding: 10px 16px; color: #1e293b;">
            {{ $serverName }} <span style="font-family: monospace; color: #64748b;">({{ $serverIp }})</span>
        </td>
    </tr>
    <tr style="background-color: #f8fafc;">
        <td style="padding: 10px 16px; font-weight: 600; color: #475569;">Incident Timestamp</td>
        <td style="padding: 10px 16px; color: #64748b; font-size: 12px;">
            {{ $checkedAt }}
        </td>
    </tr>
</table>

<!-- Troubleshooting Tips -->
<div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 24px; font-size: 12px; color: #475569; line-height: 1.5;">
    <strong style="color: #0f172a; display: block; margin-bottom: 4px;">Troubleshooting Checklist:</strong>
    <ul style="margin: 0; padding-left: 20px;">
        <li><strong>DNS A Record:</strong> Verify if the domain's DNS A record points to <span style="font-family: monospace;">{{ $serverIp }}</span> or if nameservers/DNS records have recently changed.</li>
        <li><strong>Nginx / Web Server:</strong> Check if Nginx or web service is running on the Nimbus node.</li>
        <li><strong>SSL / HTTPS:</strong> Verify Let's Encrypt certificate status and HTTPS redirect rules.</li>
    </ul>
</div>

<!-- CTA Button -->
<div class="button-wrapper" style="text-align: center; margin: 32px 0 16px 0;">
    <a href="{{ $adminUrl }}" class="btn-primary" style="background-color: #0f172a; color: #ffffff !important; border-radius: 8px; padding: 12px 28px; text-decoration: none; font-weight: 700; font-size: 13px; display: inline-block;">
        Open Nimbus Hosting Console
    </a>
</div>
@endsection
