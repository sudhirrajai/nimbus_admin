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
                'content' => "<h1>Terms of Service</h1><p>Welcome to Roook Hosting. By accessing or using our services, you agree to comply with and be bound by the following terms and conditions. Please read them carefully.</p><h3>1. Use of Services</h3><p>You agree to use Roook services only for lawful purposes. You are responsible for all activities and deployments managed under your user account.</p><h3>2. Licensing & Cloud Accounts</h3><p>Cloud server and Nimbus operations require valid credentials and active subscriptions. Sharing, tampering, or malicious activity on managed instances is strictly prohibited.</p><h3>3. Limitation of Liability</h3><p>Our platform is provided 'as is' with 99.9% uptime commitment. Roook Hosting will not be liable for any unauthorized third-party access resulting from compromised client credentials.</p>",
            ],
            [
                'slug' => 'privacy',
                'title' => 'Privacy Policy',
                'content' => "<h1>Privacy Policy</h1><p>Your privacy is important to us. This policy details how we collect, process, and protect information within the Roook Hosting platform.</p><h3>1. Information We Collect</h3><p>We collect your account registration details (name, email, phone) and active server properties solely to provision and manage your cloud infrastructure.</p><h3>2. Telemetry & Heartbeats</h3><p>Server telemetry monitors uptime, resource health, and system security status. We do not inspect or store your private database contents or sensitive user files.</p><h3>3. Data Protection</h3><p>All communication is encrypted using 256-bit TLS/SSL. We never sell, lease, or monetize your personal information to third parties.</p>",
            ],
            [
                'slug' => 'support',
                'title' => 'Help Center & Support',
                'content' => "<h1>Help Center & Support</h1><p>Need assistance with your server configuration, DNS binding, or cloud setup? Here are the quickest channels to find help.</p><h3>1. Direct Support</h3><p>If you encounter configuration questions, reach out to our dedicated operations desk at support@roook.host or open a support ticket inside your client dashboard.</p><h3>2. 2-Hour Provisioning Assistance</h3><p>All new managed hosting accounts are verified and set up by our infrastructure team within maximum 2 hours with hands-on engineer support.</p>",
            ]
        ];

        foreach ($defaultPages as $pageData) {
            \App\Models\Page::updateOrCreate(
                ['slug' => $pageData['slug']],
                ['title' => $pageData['title'], 'content' => $pageData['content']]
            );
        }

        // Seed Default Plans
        $defaultPlans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'price_inr' => 0,
                'price_usd' => 0,
                'billing_period' => 'forever',
                'max_domains' => 3,
                'features' => ['1 Server', '3 Domains Limit', 'SSL Automation', 'File Manager', 'Community Support', 'Basic Monitoring'],
                'is_active' => true,
                'is_popular' => false,
                'cta_text' => 'Start Free',
                'description' => 'Free plan for personal use.',
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'price_inr' => 499,
                'price_usd' => 19,
                'billing_period' => '/year',
                'max_domains' => 50,
                'features' => ['5 Servers Support', '50 Domains Limit', 'Git Auto-Deploy', 'Priority Support', 'Team Access', 'Advanced Security', 'WordPress Manager'],
                'is_active' => true,
                'is_popular' => true,
                'cta_text' => 'Buy Pro Now',
                'description' => 'Excellent choice for growing platforms.',
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'price_inr' => 1999,
                'price_usd' => 49,
                'billing_period' => '/year',
                'max_domains' => 9999,
                'features' => ['Unlimited Servers', '9999 Domains Limit', 'White Label Support', 'SLA Guarantee', 'Dedicated Manager', 'API Access', 'Custom Integrations'],
                'is_active' => true,
                'is_popular' => false,
                'cta_text' => 'Buy Enterprise Now',
                'description' => 'Ideal for large scale networks.',
            ]
        ];

        foreach ($defaultPlans as $planData) {
            \App\Models\Plan::updateOrCreate(
                ['slug' => $planData['slug']],
                $planData
            );
        }
    }
}
