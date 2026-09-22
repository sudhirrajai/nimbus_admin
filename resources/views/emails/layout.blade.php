<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $subject ?? 'Nimbus by VMCore' }}</title>
    <style>
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
        body { margin: 0; padding: 0; width: 100% !important; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f8fafc; padding: 40px 0; }
        .main { background-color: #ffffff; margin: 0 auto; width: 100%; max-width: 600px; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .header { background: #10B981; padding: 28px 32px; text-align: left; }
        .header-title { font-size: 20px; font-weight: 800; color: #ffffff; margin: 0; text-decoration: none; letter-spacing: -0.02em; }
        .header-sub { font-size: 11px; font-weight: 600; color: rgba(255, 255, 255, 0.85); text-transform: uppercase; letter-spacing: 0.08em; margin-top: 4px; }
        .content { padding: 36px 32px; color: #1e293b; line-height: 1.6; }
        .button-wrapper { margin: 28px 0; text-align: center; }
        .btn-primary { display: inline-block; background-color: #10B981; color: #ffffff !important; font-weight: 700; font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em; padding: 12px 28px; border-radius: 8px; text-decoration: none; box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2); }
        .footer { padding: 24px 32px; text-align: center; font-size: 12px; color: #64748b; background-color: #f8fafc; border-top: 1px solid #e2e8f0; }
        .footer a { color: #10B981; text-decoration: none; font-weight: 600; }
        @media only screen and (max-width: 620px) {
            .wrapper { padding: 12px 0 !important; }
            .main { border-radius: 0 !important; border-left: none !important; border-right: none !important; }
            .header, .content, .footer { padding: 24px 20px !important; }
            .btn-primary { width: 100% !important; box-sizing: border-box !important; text-align: center !important; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td align="center">
                    <table class="main" role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <!-- Brand Header -->
                        <tr>
                            <td class="header">
                                <div class="header-title">Nimbus <span style="font-weight: 500; opacity: 0.9;">by VMCore</span></div>
                                <div class="header-sub">Cloud Server Infrastructure &amp; Licensing</div>
                            </td>
                        </tr>

                        <!-- Main Email Content Slot -->
                        <tr>
                            <td class="content">
                                @yield('content')
                            </td>
                        </tr>

                        <!-- Footer -->
                        <tr>
                            <td class="footer">
                                <p style="margin: 0 0 8px 0;">
                                    Questions or need assistance? Reach out to us at 
                                    <a href="mailto:{{ $supportEmail ?? 'support@vmcore.in' }}">{{ $supportEmail ?? 'support@vmcore.in' }}</a>
                                </p>
                                <p style="margin: 0; font-size: 11px; color: #94a3b8;">
                                    &copy; {{ date('Y') }} Nimbus by VMCore. All rights reserved.<br/>
                                    Automated cloud infrastructure notification.
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
