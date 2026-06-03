<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\License;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminLicenseController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Licenses', [
            'licenses' => License::with('user')->latest()->get(),
        ]);
    }

    public function update(Request $request, License $license)
    {
        $request->validate([
            'status' => 'required|in:active,suspended,expired',
            'expires_at' => 'nullable|date',
        ]);

        $license->update($request->only('status', 'expires_at'));

        return back()->with('success', 'License updated successfully.');
    }

    public function destroy(License $license)
    {
        $license->delete();
        return back()->with('success', 'License deleted successfully.');
    }
}
