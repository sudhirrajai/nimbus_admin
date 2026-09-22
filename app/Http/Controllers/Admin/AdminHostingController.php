<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HostingAccount;
use App\Models\HostingRequest;
use App\Models\HostingServer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AdminHostingController extends Controller
{
    /**
     * Display managed hosting dashboard: servers, client accounts, and incoming requests.
     */
    public function index()
    {
        $servers = HostingServer::withCount('accounts')
            ->orderBy('created_at', 'desc')
            ->get();

        $accounts = HostingAccount::with(['user:id,name,email', 'server:id,name,ip_address,nimbus_url', 'latestInvoice'])
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'accounts_page');

        $requests = HostingRequest::with('user:id,name,email')
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'requests_page');

        $users = User::select('id', 'name', 'email')->orderBy('name')->get();

        $managedPlans = \App\Models\Plan::managedHosting()
            ->where('is_active', true)
            ->orderBy('price_inr')
            ->get();

        return Inertia::render('Admin/Hosting/Index', [
            'servers' => $servers,
            'accounts' => $accounts,
            'requests' => $requests,
            'users' => $users,
            'managedPlans' => $managedPlans,
        ]);
    }

    /**
     * Register a new managed Nimbus server.
     */
    public function storeServer(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip',
            'panel_url' => 'required|url',
            'api_secret' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        if (empty($validated['api_secret'])) {
            $validated['api_secret'] = Str::random(48);
        }

        HostingServer::create($validated);

        return back()->with('success', 'Hosting server registered successfully.');
    }

    /**
     * Update server details.
     */
    public function updateServer(Request $request, HostingServer $server)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip',
            'panel_url' => 'required|url',
            'api_secret' => 'required|string|max:255',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $server->update($validated);

        return back()->with('success', 'Hosting server updated successfully.');
    }

    /**
     * Remove a server.
     */
    public function destroyServer(HostingServer $server)
    {
        if ($server->accounts()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete server with assigned hosting accounts. Reassign or delete accounts first.']);
        }

        $server->delete();

        return back()->with('success', 'Hosting server deleted.');
    }

    /**
     * Assign / create a hosting account for a managed client.
     */
    public function storeAccount(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'hosting_server_id' => 'required|exists:hosting_servers,id',
            'domain' => 'required|string|max:255',
            'plan_name' => 'required|string|max:255',
            'status' => 'required|in:active,suspended,terminated',
            'notes' => 'nullable|string|max:1000',
            'billing_cycle' => 'nullable|string|in:monthly,quarterly,semi_annual,yearly,biennial,triennial',
            'starts_at' => 'nullable|date',
            'amount' => 'nullable|numeric|min:0', // initial term price (e.g. 3800)
            'renewal_price' => 'nullable|numeric|min:0', // subsequent renewal price (e.g. 4790)
            'currency' => 'nullable|string|max:10',
            'renews_at' => 'nullable|date',
            'auto_invoice' => 'nullable|boolean',
            'renewal_invoice_days' => 'nullable|integer|min:1|max:90',
            'payment_status' => 'nullable|in:paid,pending',
            'payment_method' => 'nullable|string|max:255',
        ]);

        $billingCycle = $validated['billing_cycle'] ?? 'yearly';
        $initialPrice = isset($validated['amount']) ? (float)$validated['amount'] : 0.00;
        $renewalPrice = isset($validated['renewal_price']) ? (float)$validated['renewal_price'] : $initialPrice;
        $currency = strtoupper($validated['currency'] ?? 'INR');
        $autoInvoice = $request->has('auto_invoice') ? $request->boolean('auto_invoice') : true;
        $leadDays = isset($validated['renewal_invoice_days']) ? (int)$validated['renewal_invoice_days'] : 14;

        $startsAt = !empty($validated['starts_at']) 
            ? \Carbon\Carbon::parse($validated['starts_at']) 
            : now();

        // Determine expiration / renewal date based on starts_at if not given
        $renewsAt = !empty($validated['renews_at']) 
            ? \Carbon\Carbon::parse($validated['renews_at']) 
            : (match ($billingCycle) {
                'monthly' => $startsAt->copy()->addMonth(),
                'quarterly' => $startsAt->copy()->addMonths(3),
                'semi_annual' => $startsAt->copy()->addMonths(6),
                'biennial' => $startsAt->copy()->addYears(2),
                'triennial' => $startsAt->copy()->addYears(3),
                default => $startsAt->copy()->addYear(),
            });

        $account = HostingAccount::create([
            'user_id' => $validated['user_id'],
            'server_id' => $validated['hosting_server_id'],
            'hosting_server_id' => $validated['hosting_server_id'],
            'primary_domain' => $validated['domain'],
            'domain' => $validated['domain'],
            'package_name' => $validated['plan_name'],
            'plan_name' => $validated['plan_name'],
            'status' => $validated['status'],
            'billing_cycle' => $billingCycle,
            'starts_at' => $startsAt,
            'initial_price' => $initialPrice,
            'renewal_price' => $renewalPrice,
            'currency' => $currency,
            'renews_at' => $renewsAt,
            'auto_invoice' => $autoInvoice,
            'renewal_invoice_days' => $leadDays,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Automatically generate the initial invoice for the user with synchronized start and end term dates
        $user = User::find($validated['user_id']);
        $server = HostingServer::find($validated['hosting_server_id']);
        $paymentStatus = $validated['payment_status'] ?? 'paid';
        $paymentMethod = $validated['payment_method'] ?? 'Admin Assignment';

        $formattedStart = $startsAt->format('d/m/Y');
        $formattedEnd = $renewsAt->format('d/m/Y');

        \App\Models\Invoice::create([
            'user_id' => $user->id,
            'invoice_number' => \App\Models\Invoice::generateInvoiceNumber(),
            'type' => 'hosting_plan',
            'plan_name' => $validated['plan_name'],
            'description' => "Managed Cloud Hosting for {$validated['domain']} ({$validated['plan_name']}) - Term: {$formattedStart} to {$formattedEnd}",
            'amount' => $initialPrice,
            'currency' => $currency,
            'status' => $paymentStatus,
            'payment_method' => $paymentMethod,
            'due_date' => $startsAt,
            'period_start' => $startsAt,
            'period_end' => $renewsAt,
            'paid_at' => ($paymentStatus === 'paid') ? now() : null,
            'hosting_account_id' => $account->id,
            'billing_details' => [
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'domain' => $validated['domain'],
                'server' => $server?->name,
                'billing_cycle' => $billingCycle,
                'renewal_price' => $renewalPrice,
                'notes' => $validated['notes'] ?? null,
            ],
        ]);

        return back()->with('success', 'Client hosting account and synchronized initial invoice generated successfully.');
    }

    /**
     * Update account status, start date, renewal pricing or details and sync invoice.
     */
    public function updateAccount(Request $request, HostingAccount $account)
    {
        $validated = $request->validate([
            'hosting_server_id' => 'required|exists:hosting_servers,id',
            'domain' => 'required|string|max:255',
            'plan_name' => 'required|string|max:255',
            'status' => 'required|in:active,suspended,terminated',
            'billing_cycle' => 'nullable|string|in:monthly,quarterly,semi_annual,yearly,biennial,triennial',
            'starts_at' => 'nullable|date',
            'initial_price' => 'nullable|numeric|min:0',
            'renewal_price' => 'nullable|numeric|min:0',
            'renews_at' => 'nullable|date',
            'auto_invoice' => 'nullable|boolean',
            'renewal_invoice_days' => 'nullable|integer|min:1|max:90',
            'notes' => 'nullable|string|max:1000',
        ]);

        $startsAt = !empty($validated['starts_at']) 
            ? \Carbon\Carbon::parse($validated['starts_at']) 
            : ($account->starts_at ?? $account->created_at ?? now());

        $renewsAt = !empty($validated['renews_at']) 
            ? \Carbon\Carbon::parse($validated['renews_at']) 
            : $account->renews_at;

        $account->update([
            'server_id' => $validated['hosting_server_id'],
            'hosting_server_id' => $validated['hosting_server_id'],
            'primary_domain' => $validated['domain'],
            'domain' => $validated['domain'],
            'package_name' => $validated['plan_name'],
            'plan_name' => $validated['plan_name'],
            'status' => $validated['status'],
            'billing_cycle' => $validated['billing_cycle'] ?? $account->billing_cycle ?? 'yearly',
            'starts_at' => $startsAt,
            'initial_price' => isset($validated['initial_price']) ? (float)$validated['initial_price'] : $account->initial_price,
            'renewal_price' => isset($validated['renewal_price']) ? (float)$validated['renewal_price'] : $account->renewal_price,
            'renews_at' => $renewsAt,
            'auto_invoice' => $request->has('auto_invoice') ? $request->boolean('auto_invoice') : $account->auto_invoice,
            'renewal_invoice_days' => isset($validated['renewal_invoice_days']) ? (int)$validated['renewal_invoice_days'] : ($account->renewal_invoice_days ?? 14),
            'notes' => $validated['notes'] ?? null,
        ]);

        // Synchronize with associated invoice (update dates, term in description, and amount if pending)
        $invoice = $account->invoices()->orderBy('created_at', 'asc')->first() ?? $account->latestInvoice;
        if ($invoice) {
            $formattedStart = $startsAt->format('d/m/Y');
            $formattedEnd = $renewsAt ? $renewsAt->format('d/m/Y') : '';

            $invoiceUpdate = [
                'period_start' => $startsAt,
                'period_end' => $renewsAt,
                'plan_name' => $validated['plan_name'],
                'description' => "Managed Cloud Hosting for {$validated['domain']} ({$validated['plan_name']}) - Term: {$formattedStart} to {$formattedEnd}",
            ];

            if ($invoice->status !== 'paid') {
                $invoiceUpdate['due_date'] = $startsAt;
                if (isset($validated['initial_price']) && (float)$validated['initial_price'] > 0) {
                    $invoiceUpdate['amount'] = (float)$validated['initial_price'];
                }
            }

            $billingDetails = $invoice->billing_details ?? [];
            $billingDetails['domain'] = $validated['domain'];
            $billingDetails['billing_cycle'] = $account->billing_cycle;
            $billingDetails['renewal_price'] = $account->renewal_price;
            $invoiceUpdate['billing_details'] = $billingDetails;

            $invoice->update($invoiceUpdate);
        }

        return back()->with('success', 'Hosting account and associated invoice updated successfully.');
    }

    /**
     * 1-Click Generate Renewal Invoice for a client hosting account.
     */
    public function generateRenewalInvoice(Request $request, HostingAccount $account)
    {
        $validated = $request->validate([
            'amount' => 'nullable|numeric|min:0',
            'payment_status' => 'nullable|in:paid,pending',
            'advance_renewal_date' => 'nullable|boolean',
        ]);

        $amount = isset($validated['amount']) && $validated['amount'] !== null
            ? (float) $validated['amount']
            : (float) ($account->renewal_price ?? $account->initial_price ?? 0.00);

        $status = $validated['payment_status'] ?? 'pending';

        $invoice = $account->generateRenewalInvoice($amount, $status, 'Admin Renewal');

        // If marked as paid, advance the renews_at date to the next period
        if ($status === 'paid' && $request->boolean('advance_renewal_date', true)) {
            $account->update([
                'renews_at' => $account->computeNextPeriodEnd($account->renews_at),
            ]);
        }

        return back()->with('success', "Renewal invoice {$invoice->invoice_number} (₹{$amount}) generated successfully for {$account->domain}.");
    }

    /**
     * Run the automated renewal billing check on-demand.
     */
    public function runRenewalCheck()
    {
        \Illuminate\Support\Facades\Artisan::call('invoices:generate-renewals');

        return back()->with('success', 'Automated renewal billing scan completed.');
    }

    /**
     * Delete an account.
     */
    public function destroyAccount(HostingAccount $account)
    {
        $account->delete();

        return back()->with('success', 'Hosting account deleted.');
    }

    /**
     * Update client request status (e.g. mark approved or fulfilled).
     */
    public function updateRequestStatus(Request $request, HostingRequest $hostingRequest)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected,fulfilled',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $hostingRequest->update($validated);

        return back()->with('success', 'Request status updated.');
    }

    /**
     * 1-Click Super Admin SSO into any managed Nimbus Server.
     */
    public function loginServer(HostingServer $server)
    {
        $payload = [
            'user_id' => auth()->id(),
            'email' => auth()->user()->email,
            'name' => auth()->user()->name,
            'role' => 'admin',
            'timestamp' => time(),
            'nonce' => Str::random(32),
        ];

        $data = base64_encode(json_encode($payload));
        $signature = hash_hmac('sha256', $data, $server->api_secret);
        $token = $data . '.' . $signature;

        $redirectUrl = rtrim($server->panel_url, '/') . '/sso/login?token=' . urlencode($token);

        return redirect()->away($redirectUrl);
    }

    /**
     * 1-Click Super Admin SSO into a specific client hosting account on Nimbus.
     */
    public function loginAccount(HostingAccount $account)
    {
        $server = $account->server;
        if (!$server) {
            return back()->withErrors(['error' => 'Associated hosting server not found.']);
        }

        $payload = [
            'user_id' => $account->user_id,
            'email' => $account->user ? $account->user->email : 'client@' . $account->domain,
            'name' => $account->user ? $account->user->name : 'Client',
            'role' => 'client',
            'domain' => $account->domain,
            'timestamp' => time(),
            'nonce' => Str::random(32),
        ];

        $data = base64_encode(json_encode($payload));
        $signature = hash_hmac('sha256', $data, $server->api_secret);
        $token = $data . '.' . $signature;

        $redirectUrl = rtrim($server->panel_url, '/') . '/sso/login?token=' . urlencode($token);

        return redirect()->away($redirectUrl);
    }
}
