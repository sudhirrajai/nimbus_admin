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
        $licenses = License::with(['user', 'planDetails'])->latest()->get()->map(function ($license) {
            $license->effective_modules = $license->getEffectiveModules();
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
            'expires_at' => 'nullable|date',
            'feature_overrides' => 'nullable|array',
        ]);

        $user = User::where('email', $request->email)->first();

        License::create([
            'user_id' => $user->id,
            'license_key' => License::generateKey($request->plan),
            'plan' => $request->plan,
            'feature_overrides' => $request->feature_overrides,
            'status' => 'active',
            'expires_at' => $request->expires_at,
            'status_changed_at' => now(),
        ]);

        return back()->with('success', 'License generated successfully.');
    }

    public function update(Request $request, License $license)
    {
        $request->validate([
            'status' => 'required|in:active,suspended,expired,revoked',
            'plan' => 'required|string',
            'expires_at' => 'nullable|date',
            'feature_overrides' => 'nullable|array',
        ]);

        $license->update(array_merge(
            $request->only('status', 'plan', 'expires_at', 'feature_overrides'),
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

    public function destroy(License $license)
    {
        $license->delete();
        return back()->with('success', 'License deleted successfully.');
    }
}
