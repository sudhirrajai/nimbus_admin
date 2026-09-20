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

        $accounts = HostingAccount::with(['user:id,name,email', 'server:id,name,ip_address,nimbus_url'])
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'accounts_page');

        $requests = HostingRequest::with('user:id,name,email')
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'requests_page');

        $users = User::select('id', 'name', 'email')->orderBy('name')->get();

        return Inertia::render('Admin/Hosting/Index', [
            'servers' => $servers,
            'accounts' => $accounts,
            'requests' => $requests,
            'users' => $users,
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
            'amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'payment_status' => 'nullable|in:paid,pending',
            'payment_method' => 'nullable|string|max:255',
        ]);

        $account = HostingAccount::create([
            'user_id' => $validated['user_id'],
            'server_id' => $validated['hosting_server_id'],
            'hosting_server_id' => $validated['hosting_server_id'],
            'primary_domain' => $validated['domain'],
            'domain' => $validated['domain'],
            'package_name' => $validated['plan_name'],
            'plan_name' => $validated['plan_name'],
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        // Automatically generate an invoice for the user
        $user = User::find($validated['user_id']);
        $server = HostingServer::find($validated['hosting_server_id']);
        $amount = isset($validated['amount']) ? (float)$validated['amount'] : 0.00;
        $currency = strtoupper($validated['currency'] ?? 'INR');
        $paymentStatus = $validated['payment_status'] ?? 'paid';
        $paymentMethod = $validated['payment_method'] ?? 'Admin Assignment';

        \App\Models\Invoice::create([
            'user_id' => $user->id,
            'invoice_number' => \App\Models\Invoice::generateInvoiceNumber(),
            'type' => 'hosting_plan',
            'plan_name' => $validated['plan_name'],
            'description' => "Managed Cloud Hosting for {$validated['domain']} ({$validated['plan_name']})",
            'amount' => $amount,
            'currency' => $currency,
            'status' => $paymentStatus,
            'payment_method' => $paymentMethod,
            'paid_at' => ($paymentStatus === 'paid') ? now() : null,
            'hosting_account_id' => $account->id,
            'billing_details' => [
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'domain' => $validated['domain'],
                'server' => $server?->name,
                'notes' => $validated['notes'] ?? null,
            ],
        ]);

        return back()->with('success', 'Client hosting account and invoice generated successfully.');
    }

    /**
     * Update account status or details.
     */
    public function updateAccount(Request $request, HostingAccount $account)
    {
        $validated = $request->validate([
            'hosting_server_id' => 'required|exists:hosting_servers,id',
            'domain' => 'required|string|max:255',
            'plan_name' => 'required|string|max:255',
            'status' => 'required|in:active,suspended,terminated',
            'notes' => 'nullable|string|max:1000',
        ]);

        $account->update($validated);

        return back()->with('success', 'Hosting account updated successfully.');
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
