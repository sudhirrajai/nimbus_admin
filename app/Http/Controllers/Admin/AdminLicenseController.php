<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminLicenseController extends Controller
{
    public function generate(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'plan' => 'required|in:free,pro,enterprise',
            'expires_at' => 'nullable|date',
        ]);

        $user = User::where('email', $request->email)->first();

        License::create([
            'user_id' => $user->id,
            'license_key' => License::generateKey($request->plan),
            'plan' => $request->plan,
            'status' => 'active',
            'expires_at' => $request->expires_at,
            'status_changed_at' => now(),
        ]);

        return back()->with('success', 'License generated successfully.');
    }
    public function index()
    {
        return Inertia::render('Admin/Licenses', [
            'licenses' => License::with('user')->latest()->get(),
        ]);
    }

    public function update(Request $request, License $license)
    {
        $request->validate([
            'status' => 'required|in:active,suspended,expired,revoked',
            'plan' => 'required|in:free,pro,enterprise',
            'expires_at' => 'nullable|date',
        ]);

        $license->update(array_merge(
            $request->only('status', 'plan', 'expires_at'),
            ['status_changed_at' => now()]
        ));

        return back()->with('success', 'License updated successfully.');
    }

    public function destroy(License $license)
    {
        $license->delete();
        return back()->with('success', 'License deleted successfully.');
    }
}
