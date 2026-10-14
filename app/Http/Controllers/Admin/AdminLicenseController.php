<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminLicenseController extends Controller
{
    public function index()
    {
        $licenses = License::with(['user', 'planDetails', 'activations'])->latest()->get()->map(function ($license) {
            $license->effective_modules = $license->getEffectiveModules();
            $license->max_servers_computed = $license->getMaxServers();
            return $license;
        });

        return Inertia::render('Admin/Licenses', [
            'licenses' => $licenses,
            'plans' => Plan::where('is_active', true)->get(),
            'availableModules' => Plan::AVAILABLE_MODULES,
        ]);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'plan' => 'required|string',
            'max_servers' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'feature_overrides' => 'nullable|array',
        ]);

        $user = User::where('email', $request->email)->first();

        $license = License::create([
            'user_id' => $user->id,
            'license_key' => License::generateKey($request->plan),
            'plan' => $request->plan,
            'max_servers' => $request->max_servers,
            'feature_overrides' => $request->feature_overrides,
            'status' => 'active',
            'expires_at' => $request->expires_at,
            'status_changed_at' => now(),
        ]);

        // Generate invoice record for this assigned license
        $planModel = Plan::where('slug', $request->plan)->first();
        $planName = $planModel->name ?? (ucfirst($request->plan) . ' Plan');
        $amount = $planModel ? (float) $planModel->price_inr : 0.00;

        \App\Models\Invoice::create([
            'user_id' => $user->id,
            'invoice_number' => \App\Models\Invoice::generateInvoiceNumber(),
            'type' => 'license_plan',
            'plan_name' => $planName,
            'description' => "Nimbus {$planName} Server License (Admin Generated)",
            'amount' => $amount,
            'currency' => 'INR',
            'status' => 'paid',
            'payment_method' => 'Admin Assignment',
            'paid_at' => now(),
            'license_id' => $license->id,
            'billing_details' => [
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'license_key' => $license->license_key,
            ],
        ]);

        return back()->with('success', 'License and invoice generated successfully.');
    }

    public function update(Request $request, License $license)
    {
        $request->validate([
            'status' => 'required|in:active,suspended,expired,revoked',
            'plan' => 'required|string',
            'max_servers' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'feature_overrides' => 'nullable|array',
        ]);

        $license->update(array_merge(
            $request->only('status', 'plan', 'max_servers', 'expires_at', 'feature_overrides'),
            ['status_changed_at' => now()]
        ));

        return back()->with('success', 'License updated successfully.');
    }

    public function updateFeatures(Request $request, License $license)
    {
        $request->validate([
            'feature_overrides' => 'nullable|array',
        ]);

        $license->update([
            'feature_overrides' => $request->feature_overrides,
            'status_changed_at' => now(),
        ]);

        return back()->with('success', "Feature overrides updated for {$license->license_key}.");
    }

    public function disconnectActivation(\App\Models\LicenseActivation $activation)
    {
        $license = $activation->license;
        $serverIp = $activation->server_ip;
        $activation->delete();

        if ($license && $license->activations()->count() === 0) {
            $license->update([
                'server_ip' => null,
                'machine_id' => null,
                'domain' => null,
                'status_changed_at' => now(),
            ]);
        }

        return back()->with('success', "Server {$serverIp} disconnected successfully from license {$license->license_key}.");
    }

    public function destroy(License $license)
    {
        $license->delete();
        return back()->with('success', 'License deleted successfully.');
    }
}
