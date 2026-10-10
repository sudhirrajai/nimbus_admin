<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class AdminSettingsController extends Controller
{
    public static function getSettingsPath()
    {
        return 'settings.json';
    }

    public static function getSettings()
    {
        $path = self::getSettingsPath();
        $defaults = [
            'site_name' => 'Roook Hosting',
            'allow_registration' => true,
            'maintenance_mode' => false,
            'free_license_limit' => 1,
            'license_expiry_days' => 30,
            'razorpay_enabled' => true,
            'razorpay_mode' => 'sandbox',
            'company_name' => 'Roook Hosting',
            'company_address_line1' => '#104, Tech Park Boulevard',
            'company_address_line2' => 'Bangalore - 560038, Karnataka, India',
            'company_email' => 'billing@roook.host',
            'company_phone' => '+91 80 4567 8900',
            'company_website' => 'https://roook.host',
            'bank_name' => 'HDFC Bank Ltd.',
            'bank_account_name' => 'Roook Hosting',
            'bank_account' => '50200088991122',
            'bank_ifsc' => 'HDFC0001234',
            'bank_branch' => 'Indiranagar Branch, Bangalore',
            'bank_upi' => 'roook@hdfcbank',

            // Global SEO & Social Sharing Metadata
            'meta_title' => 'Roook Hosting — Managed Cloud Servers',
            'meta_description' => 'High-performance managed cloud hosting with NVMe SSD infrastructure, isolated Docker architecture, automated daily backups, and a 2-hour provisioning SLA.',
            'meta_keywords' => 'managed cloud hosting, nimbus control panel, nvme cloud servers, fast hosting, roook hosting, dedicated servers, linux server management',
            'meta_author' => 'Roook Hosting',
            'og_image' => '/og-image.png',
            'twitter_handle' => '@roookhost',
            'google_site_verification' => '',
            'bing_site_verification' => '',
            'robots_index' => true,
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
            'settings' => self::getSettings()
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

            // SEO Metadata Validation
            'meta_title' => 'nullable|string|max:150',
            'meta_description' => 'nullable|string|max:300',
            'meta_keywords' => 'nullable|string|max:300',
            'meta_author' => 'nullable|string|max:100',
            'og_image' => 'nullable|string|max:255',
            'twitter_handle' => 'nullable|string|max:50',
            'google_site_verification' => 'nullable|string|max:150',
            'bing_site_verification' => 'nullable|string|max:150',
            'robots_index' => 'required|boolean',
        ]);

        Storage::disk('local')->put(self::getSettingsPath(), json_encode($validated, JSON_PRETTY_PRINT));

        return back()->with('success', 'System, invoice, and SEO settings updated successfully.');
    }
}
