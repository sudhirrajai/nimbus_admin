@extends('emails.layout')

@section('content')
<h1 style="font-size: 20px; font-weight: 800; color: #0f172a; margin: 0 0 16px 0; letter-spacing: -0.02em;">
    Verify Your Email Address
</h1>

<p style="font-size: 14px; color: #334155; margin: 0 0 16px 0;">
    Hello {{ $user->name ?? 'there' }},
</p>

<p style="font-size: 14px; color: #334155; margin: 0 0 24px 0;">
    Welcome to <strong>Nimbus by VMCore</strong>! Please verify your email address to activate your account and securely deploy cloud servers or manage your self-hosted licenses.
</p>

<div class="button-wrapper">
    <a href="{{ $verificationUrl }}" class="btn-primary" target="_blank">
        Verify Email Address
    </a>
</div>

<p style="font-size: 12px; color: #64748b; margin: 24px 0 0 0; border-top: 1px solid #f1f5f9; padding-top: 16px;">
    <strong>Security Notice:</strong> This verification link will expire in 60 minutes. If you did not create an account on Nimbus, please ignore this email or contact support.
</p>

<p style="font-size: 11px; color: #94a3b8; word-break: break-all; margin-top: 12px;">
    Having trouble clicking the button? Copy and paste the following link into your browser:<br/>
    <a href="{{ $verificationUrl }}" style="color: #10B981; text-decoration: underline;">{{ $verificationUrl }}</a>
</p>
@endsection
