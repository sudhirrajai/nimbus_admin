<?php

namespace App\Http\Controllers;

use App\Models\HostingAccount;
use App\Models\HostingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HostingController extends Controller
{
    /**
     * Submit a new managed hosting inquiry/request.
     */
    public function submitRequest(Request $request)
    {
        $validated = $request->validate([
            'domain' => 'nullable|string|max:255',
            'plan_requested' => 'nullable|string|max:255',
            'estimated_traffic' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
        ]);

        HostingRequest::create([
            'user_id' => auth()->id(),
            'domain' => $validated['domain'] ?? null,
            'plan_requested' => $validated['plan_requested'] ?? 'Standard Managed Hosting',
            'estimated_traffic' => $validated['estimated_traffic'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Your managed hosting request has been received! Our team will configure your Nimbus environment and notify you shortly.');
    }

    /**
     * Client 1-Click SSO Login into their assigned Nimbus panel instance.
     */
    public function ssoLogin(HostingAccount $account)
    {
        // Enforce user authorization: only the account owner can use this client SSO
        if ($account->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this hosting account.');
        }

        if ($account->status !== 'active') {
            return back()->withErrors(['error' => 'This hosting account is currently ' . $account->status . '. Please contact support.']);
        }

        $server = $account->server;
        if (!$server || !$server->is_active) {
            return back()->withErrors(['error' => 'The hosting server is temporarily unavailable or undergoing maintenance.']);
        }

        // Generate HMAC-signed payload for seamless single sign-on
        $payload = [
            'user_id' => auth()->id(),
            'email' => auth()->user()->email,
            'name' => auth()->user()->name,
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
