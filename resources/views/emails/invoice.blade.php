@extends('emails.layout')

@section('content')
<div style="margin-bottom: 24px;">
    <span style="display: inline-block; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; padding: 4px 10px; border-radius: 9999px; {{ $invoice->status === 'paid' ? 'background-color: #d1fae5; color: #047857;' : 'background-color: #fef3c7; color: #b45309;' }}">
        {{ $invoice->status === 'paid' ? 'Official Receipt &bull; Paid' : 'Invoice &bull; Payment Due' }}
    </span>
    <h1 style="font-size: 22px; font-weight: 800; color: #0f172a; margin: 12px 0 4px 0; letter-spacing: -0.02em;">
        Invoice #{{ $invoice->invoice_number }}
    </h1>
    <p style="font-size: 12px; color: #64748b; margin: 0;">
        Date: {{ $invoice->created_at->format('M d, Y') }}
    </p>
</div>

<p style="font-size: 14px; color: #334155; margin: 0 0 20px 0;">
    Hello {{ $invoice->billing_details['customer_name'] ?? $invoice->user?->name ?? 'Valued Client' }},
</p>

<p style="font-size: 14px; color: #334155; margin: 0 0 24px 0;">
    {{ $invoice->status === 'paid' 
        ? 'Thank you for your payment. Here is your official payment receipt for your Nimbus services.' 
        : 'A new invoice has been generated for your Nimbus server services. Please find the billing details below.' }}
</p>

<!-- Invoice Breakdown Table -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse; margin-bottom: 24px; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
    <thead>
        <tr style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
            <th align="left" style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.05em;">Description</th>
            <th align="right" style="padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.05em;">Amount</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="padding: 16px; font-size: 13px; color: #1e293b; border-bottom: 1px solid #f1f5f9;">
                <div style="font-weight: 700; font-size: 14px; color: #0f172a;">{{ $invoice->plan_name }}</div>
                <div style="font-size: 12px; color: #64748b; margin-top: 4px;">{{ $invoice->description }}</div>
            </td>
            <td align="right" style="padding: 16px; font-size: 14px; font-weight: 700; font-family: monospace; color: #0f172a; border-bottom: 1px solid #f1f5f9;">
                {{ $invoice->currency === 'INR' ? '₹' : '$' }}{{ number_format($invoice->amount, 2) }}
            </td>
        </tr>
        <tr style="background-color: #f8fafc;">
            <td style="padding: 14px 16px; font-size: 13px; font-weight: 700; color: #0f172a;">Total Amount</td>
            <td align="right" style="padding: 14px 16px; font-size: 16px; font-weight: 800; font-family: monospace; color: #10B981;">
                {{ $invoice->currency === 'INR' ? '₹' : '$' }}{{ number_format($invoice->amount, 2) }} {{ $invoice->currency }}
            </td>
        </tr>
    </tbody>
</table>

<!-- Transaction & Payment Meta -->
<div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 24px;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="font-size: 12px; color: #334155;">
        <tr>
            <td style="padding: 4px 0; color: #64748b; font-weight: 600;">Payment Gateway:</td>
            <td align="right" style="padding: 4px 0; font-weight: 700; color: #0f172a;">{{ $invoice->payment_method ?? 'Online Payment' }}</td>
        </tr>
        <tr>
            <td style="padding: 4px 0; color: #64748b; font-weight: 600;">Transaction ID:</td>
            <td align="right" style="padding: 4px 0; font-weight: 700; font-family: monospace; color: #10B981;">
                {{ $invoice->payment_id ? $invoice->payment_id : ($invoice->status === 'paid' ? 'Processed' : 'Pending') }}
            </td>
        </tr>
        @if($invoice->paid_at)
        <tr>
            <td style="padding: 4px 0; color: #64748b; font-weight: 600;">Paid On:</td>
            <td align="right" style="padding: 4px 0; font-weight: 600; color: #0f172a;">{{ $invoice->paid_at->format('M d, Y h:i A') }}</td>
        </tr>
        @endif
        @if($invoice->due_date && $invoice->status !== 'paid')
        <tr>
            <td style="padding: 4px 0; color: #b45309; font-weight: 600;">Due Date:</td>
            <td align="right" style="padding: 4px 0; font-weight: 700; color: #b45309;">{{ $invoice->due_date->format('M d, Y') }}</td>
        </tr>
        @endif
    </table>
</div>

<div class="button-wrapper">
    <a href="{{ route('invoices.show', $invoice->uuid ?? $invoice->id) }}" class="btn-primary" target="_blank">
        View &amp; Print Full Invoice
    </a>
</div>
@endsection
