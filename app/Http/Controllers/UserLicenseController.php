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

        return Inertia::render('Dashboard', [
            'licenses' => $licenses
        ]);
    }

    public function generateFree(Request $request)
    {
        License::create([
            'user_id' => auth()->id(),
            'license_key' => License::generateKey('free'),
            'plan' => 'free',
            'status' => 'active',
            'expires_at' => null,
        ]);

        return back()->with('success', 'Free license generated successfully.');
    }
}
