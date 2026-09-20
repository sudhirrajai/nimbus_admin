<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices for the authenticated user.
     */
    public function index()
    {
        $invoices = Invoice::where('user_id', auth()->id())
            ->with(['license:id,license_key,plan', 'hostingAccount'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Invoices/Index', [
            'invoices' => $invoices,
        ]);
    }

    /**
     * Display the specified invoice (printable / downloadable receipt).
     */
    public function show(Invoice $invoice)
    {
        // Enforce access control
        if ($invoice->user_id !== auth()->id() && !auth()->user()->is_admin) {
            abort(403, 'Unauthorized access to invoice.');
        }

        $invoice->load(['user:id,uuid,name,email,company_name,phone', 'license', 'hostingAccount.server']);

        // Load company configuration
        $settings = [];
        if (Storage::disk('local')->exists('settings.json')) {
            $settings = json_decode(Storage::disk('local')->get('settings.json'), true) ?: [];
        }

        $company = [
            'name' => $settings['company_name'] ?? 'VMCore Technologies Pvt. Ltd.',
            'brand' => $settings['site_name'] ?? 'Nimbus Cloud Platform',
            'tagline' => 'Enterprise Cloud Infrastructure & Managed Hosting Solutions',
            'address_line1' => $settings['company_address_line1'] ?? '#104, Tech Park Boulevard',
            'address_line2' => $settings['company_address_line2'] ?? 'Indiranagar, Bangalore, Karnataka - 560038, India',
            'gstin' => $settings['company_gstin'] ?? '29AADCV1234F1Z5',
            'pan' => $settings['company_pan'] ?? 'AADCV1234F',
            'email' => $settings['company_email'] ?? 'billing@vmcore.in',
            'support_email' => $settings['company_email'] ?? 'support@vmcore.in',
            'phone' => $settings['company_phone'] ?? '+91 (0) 80-4567-8900',
            'website' => $settings['company_website'] ?? 'https://nimbus.vmcore.in',
            'bank_name' => $settings['bank_name'] ?? 'HDFC Bank Ltd.',
            'bank_account' => $settings['bank_account'] ?? '50200088991122',
            'bank_ifsc' => $settings['bank_ifsc'] ?? 'HDFC0001234',
            'bank_branch' => $settings['bank_branch'] ?? 'Indiranagar Branch, Bangalore',
            'bank_upi' => $settings['bank_upi'] ?? 'vmcore@hdfcbank',
            'invoice_terms' => $settings['invoice_terms'] ?? "1. All hosting services and server licenses are billed in advance for the committed period.\n2. Cloud services renew automatically at agreed renewal rates unless cancelled 14 days prior to due date.\n3. This is an electronically generated Tax Invoice under Section 13(2) of the Information Technology Act, 2000 and requires no physical signature.",
        ];

        return Inertia::render('Invoices/Show', [
            'invoice' => $invoice,
            'company' => $company,
            'isAdmin' => (bool) auth()->user()->is_admin,
        ]);
    }
}
