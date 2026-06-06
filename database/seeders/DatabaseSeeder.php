<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed Admin User
        if (!User::where('email', 'admin@vmcore.in')->exists()) {
            User::create([
                'name' => 'Admin',
                'email' => 'admin@vmcore.in',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]);
        }

        // Seed Test User
        if (!User::where('email', 'test@example.com')->exists()) {
            User::create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'is_admin' => false,
                'email_verified_at' => now(),
            ]);
        }

        // Seed Default Pages
        $defaultPages = [
            [
                'slug' => 'terms',
                'title' => 'Terms of Service',
                'content' => "<h1>Terms of Service</h1><p>Welcome to Nimbus by VMCore. By accessing or using our services, you agree to comply with and be bound by the following terms and conditions. Please read them carefully.</p><h3>1. Use of Services</h3><p>You agree to use Nimbus services only for lawful purposes. You are responsible for all activities and deployments managed under your user account.</p><h3>2. Licensing</h3><p>Nimbus server panel operations require a valid cryptographic license key generated via VmCoreCentral. Sharing, cracking, or tampering with licensing tokens is strictly prohibited.</p><h3>3. Limitation of Liability</h3><p>Our platform is provided 'as is' without warranties of any kind. VMCore will not be liable for any data loss, service interruption, or security incidents on your managed servers.</p>",
            ],
            [
                'slug' => 'privacy',
                'title' => 'Privacy Policy',
                'content' => "<h1>Privacy Policy</h1><p>Your privacy is important to us. This policy details how we collect, process, and protect information within the Nimbus platform and licensing central.</p><h3>1. Information We Collect</h3><p>We collect your registration details (name, email) and active server properties (such as machine identifiers and server IPs) solely to validate licenses and secure heartbeats.</p><h3>2. Licensing Logs</h3><p>Licensing heartbeats are pinged every 5 minutes from your Nimbus panels to log platform connectivity status. We do not store any database keys, files, or sensitive secrets from your panel nodes.</p><h3>3. Data Protection</h3><p>All communication between Nimbus panels and our licensing servers is encrypted over SSL. We do not sell or lease your personal information to third parties.</p>",
            ],
            [
                'slug' => 'support',
                'title' => 'Help Center & Support',
                'content' => "<h1>Help Center & Support</h1><p>Need assistance with your server configuration or licensing setup? Here are the quickest channels to find help.</p><h3>1. Documentation</h3><p>The official guide covers all aspects of installation, domain binding, reverse proxies, and system maintenance. Visit <a href='https://nimbus-docs.vmcore.in/' target='_blank'>Nimbus Documentation</a>.</p><h3>2. Active Support</h3><p>If you encounter configuration problems, reach out to our dedicated operations desk at support@vmcore.in or raise an issue inside the customer console panel.</p>",
            ]
        ];

        foreach ($defaultPages as $pageData) {
            \App\Models\Page::updateOrCreate(
                ['slug' => $pageData['slug']],
                ['title' => $pageData['title'], 'content' => $pageData['content']]
            );
        }
    }
}
