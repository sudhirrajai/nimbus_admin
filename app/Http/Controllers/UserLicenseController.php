<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\License;
use Inertia\Inertia;

class UserLicenseController extends Controller
{
    public function index()
    {
        $licenses = License::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        $plans = \App\Models\Plan::where('is_active', true)
            ->orderBy('price_inr')
            ->get();

        $hostingAccounts = \App\Models\HostingAccount::where('user_id', auth()->id())
            ->with('server:id,name,ip_address,nimbus_url')
            ->orderBy('created_at', 'desc')
            ->get();

        $hostingRequests = \App\Models\HostingRequest::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Dashboard', [
            'licenses' => $licenses,
            'plans' => $plans,
            'hostingAccounts' => $hostingAccounts,
            'hostingRequests' => $hostingRequests,
            'availableModules' => \App\Models\Plan::AVAILABLE_MODULES,
        ]);
    }

    public function generateFree(Request $request)
    {
        $existingFreeLicense = License::where('user_id', auth()->id())
            ->where('plan', 'free')
            ->where('status', 'active')
            ->first();

        if ($existingFreeLicense) {
            return back()->withErrors(['error' => 'You already have an active free license.']);
        }

        License::create([
            'user_id' => auth()->id(),
            'license_key' => License::generateKey('free'),
            'plan' => 'free',
            'status' => 'active',
            'expires_at' => null,
            'status_changed_at' => now(),
        ]);

        return back()->with('success', 'Free license generated successfully.');
    }

    public function disconnect(License $license)
    {
        if ($license->user_id !== auth()->id()) {
            abort(403);
        }

        if ($license->status !== 'active') {
            return back()->withErrors(['error' => 'Only active licenses can be disconnected.']);
        }

        $license->update([
            'server_ip' => null,
            'machine_id' => null,
            'domain' => null,
            'admin_name' => null,
            'admin_email' => null,
            'status_changed_at' => now(),
        ]);

        return back()->with('success', 'Machine installation disconnected successfully.');
    }

    public function revoke(License $license)
    {
        if ($license->user_id !== auth()->id()) {
            abort(403);
        }

        if ($license->status !== 'active') {
            return back()->withErrors(['error' => 'Only active licenses can be revoked.']);
        }

        $license->update([
            'status' => 'revoked',
            'status_changed_at' => now(),
        ]);

        return back()->with('success', 'License revoked successfully. Devices using this license will stop working.');
    }

    public function subscription()
    {
        $licenses = License::where('user_id', auth()->id())
            ->where('status', 'active')
            ->with('planDetails')
            ->orderBy('created_at', 'desc')
            ->get();

        $hostingAccounts = \App\Models\HostingAccount::where('user_id', auth()->id())
            ->with(['server:id,name,ip_address,nimbus_url', 'latestInvoice'])
            ->orderBy('created_at', 'desc')
            ->get();

        $invoices = \App\Models\Invoice::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return Inertia::render('Subscription', [
            'licenses' => $licenses,
            'hostingAccounts' => $hostingAccounts,
            'invoices' => $invoices,
        ]);
    }
}
