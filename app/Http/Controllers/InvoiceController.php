<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices for the authenticated user.
     */
    public function index()
    {
        $invoices = Invoice::where('user_id', auth()->id())
            ->with(['license:id,license_key,plan', 'hostingAccount:id,domain,plan_name'])
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

        $invoice->load(['user:id,name,email', 'license', 'hostingAccount.server']);

        return Inertia::render('Invoices/Show', [
            'invoice' => $invoice,
            'isAdmin' => (bool) auth()->user()->is_admin,
        ]);
    }
}
