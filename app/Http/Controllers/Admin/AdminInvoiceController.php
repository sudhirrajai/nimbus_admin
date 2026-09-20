<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['user:id,name,email', 'license:id,license_key,plan', 'hostingAccount'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'LIKE', "%{$search}%")
                         ->orWhere('email', 'LIKE', "%{$search}%");
                  });
            });
        }

        $invoices = $query->paginate(20)->withQueryString();

        $stats = [
            'total_revenue' => (float) Invoice::where('status', 'paid')->sum('amount'),
            'total_invoices' => Invoice::count(),
            'paid_invoices' => Invoice::where('status', 'paid')->count(),
            'pending_invoices' => Invoice::where('status', 'pending')->count(),
        ];

        $users = User::select('id', 'name', 'email')->orderBy('name')->get();

        return Inertia::render('Admin/Invoices/Index', [
            'invoices' => $invoices,
            'filters' => $request->only(['status', 'search']),
            'stats' => $stats,
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:license_plan,hosting_plan,custom',
            'plan_name' => 'nullable|string|max:255',
            'description' => 'required|string|max:1000',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|max:10',
            'status' => 'required|in:paid,pending,cancelled',
            'payment_method' => 'required|string|max:255',
            'payment_id' => 'nullable|string|max:255',
        ]);

        $user = User::findOrFail($validated['user_id']);

        $invoice = Invoice::create([
            'user_id' => $user->id,
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'type' => $validated['type'],
            'plan_name' => $validated['plan_name'] ?? 'Custom Invoice',
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'currency' => strtoupper($validated['currency']),
            'status' => $validated['status'],
            'payment_method' => $validated['payment_method'],
            'payment_id' => $validated['payment_id'] ?? null,
            'paid_at' => $validated['status'] === 'paid' ? now() : null,
            'billing_details' => [
                'customer_name' => $user->name,
                'customer_email' => $user->email,
            ],
        ]);

        return back()->with('success', "Invoice {$invoice->invoice_number} created successfully.");
    }

    public function updateStatus(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'status' => 'required|in:paid,pending,cancelled',
        ]);

        $updateData = ['status' => $validated['status']];
        if ($validated['status'] === 'paid' && !$invoice->paid_at) {
            $updateData['paid_at'] = now();
        }

        $invoice->update($updateData);

        return back()->with('success', "Invoice {$invoice->invoice_number} status updated to {$validated['status']}.");
    }

    public function destroy(Invoice $invoice)
    {
        $number = $invoice->invoice_number;
        $invoice->delete();

        return back()->with('success', "Invoice {$number} deleted successfully.");
    }
}
