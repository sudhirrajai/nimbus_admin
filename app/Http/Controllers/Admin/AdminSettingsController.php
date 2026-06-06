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
        if (!Storage::disk('local')->exists($path)) {
            $defaults = [
                'site_name' => 'Nimbus by VMCore',
                'allow_registration' => true,
                'maintenance_mode' => false,
                'free_license_limit' => 1,
                'license_expiry_days' => 30,
                'razorpay_enabled' => true,
                'razorpay_mode' => 'sandbox',
            ];
            Storage::disk('local')->put($path, json_encode($defaults, JSON_PRETTY_PRINT));
            return $defaults;
        }
        
        $settings = json_decode(Storage::disk('local')->get($path), true);
        
        // Ensure defaults are present in case the file was partially created
        return array_merge([
            'site_name' => 'Nimbus by VMCore',
            'allow_registration' => true,
            'maintenance_mode' => false,
            'free_license_limit' => 1,
            'license_expiry_days' => 30,
            'razorpay_enabled' => true,
            'razorpay_mode' => 'sandbox',
        ], $settings);
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
            'site_name' => 'required|string|max:50',
            'allow_registration' => 'required|boolean',
            'maintenance_mode' => 'required|boolean',
            'free_license_limit' => 'required|integer|min:0|max:100',
            'license_expiry_days' => 'required|integer|min:1|max:3650',
            'razorpay_enabled' => 'required|boolean',
            'razorpay_mode' => 'required|in:sandbox,live',
        ]);

        Storage::disk('local')->put($this->getSettingsPath(), json_encode($validated, JSON_PRETTY_PRINT));

        return back()->with('success', 'System settings updated successfully.');
    }
}
