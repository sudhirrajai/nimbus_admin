<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class AdminSettingsController extends Controller
{
    private function getSettingsPath()
    {
        return 'settings.json';
    }

    private function getSettings()
    {
        $path = $this->getSettingsPath();
        $defaults = [
            'site_name' => 'Nimbus by VMCore',
            'allow_registration' => true,
            'maintenance_mode' => false,
            'free_license_limit' => 1,
            'license_expiry_days' => 30,
            'razorpay_enabled' => true,
            'razorpay_mode' => 'sandbox',
            'company_name' => 'Nimbus by VMCore',
            'company_address_line1' => '#104, Tech Park Boulevard',
            'company_address_line2' => 'Bangalore - 560038, Karnataka, India',
            'company_email' => 'billing@vmcore.in',
            'company_phone' => '+91 80 4567 8900',
            'company_website' => 'https://nimbus.vmcore.in',
            'bank_name' => 'HDFC Bank Ltd.',
            'bank_account_name' => 'Nimbus by VMCore',
            'bank_account' => '50200088991122',
            'bank_ifsc' => 'HDFC0001234',
            'bank_branch' => 'Indiranagar Branch, Bangalore',
            'bank_upi' => 'vmcore@hdfcbank',
        ];

        if (!Storage::disk('local')->exists($path)) {
            Storage::disk('local')->put($path, json_encode($defaults, JSON_PRETTY_PRINT));
            return $defaults;
        }
        
        $settings = json_decode(Storage::disk('local')->get($path), true) ?: [];
        
        return array_merge($defaults, $settings);
    }

    public function index()
    {
        return Inertia::render('Admin/Settings', [
            'settings' => $this->getSettings()
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:100',
            'allow_registration' => 'required|boolean',
            'maintenance_mode' => 'required|boolean',
            'free_license_limit' => 'required|integer|min:0|max:100',
            'license_expiry_days' => 'required|integer|min:1|max:3650',
            'razorpay_enabled' => 'required|boolean',
            'razorpay_mode' => 'required|in:sandbox,live',
            'company_name' => 'nullable|string|max:150',
            'company_address_line1' => 'nullable|string|max:200',
            'company_address_line2' => 'nullable|string|max:200',
            'company_email' => 'nullable|email|max:100',
            'company_phone' => 'nullable|string|max:50',
            'company_website' => 'nullable|string|max:150',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_name' => 'nullable|string|max:150',
            'bank_account' => 'nullable|string|max:50',
            'bank_ifsc' => 'nullable|string|max:30',
            'bank_branch' => 'nullable|string|max:100',
            'bank_upi' => 'nullable|string|max:100',
        ]);

        Storage::disk('local')->put($this->getSettingsPath(), json_encode($validated, JSON_PRETTY_PRINT));

        return back()->with('success', 'System and tax invoice settings updated successfully.');
    }
}
