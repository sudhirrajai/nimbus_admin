<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class NimbusPricingSeeder extends Seeder
{
    /**
     * Seed or update the self-hosted Nimbus Control Panel plans and pricing.
     * Includes Monthly, 1-Year, 2-Year, and 3-Year rates for INR and USD.
     *
     * Usage:
     *   php artisan db:seed --class=NimbusPricingSeeder
     */
    public function run(): void
    {
        $nimbusPlans = [
            // ==========================================
            // 1. FREE COMMUNITY LICENSE (1 Server)
            // ==========================================
            [
                'slug'                      => 'free',
                'name'                      => 'Free',
                'type'                      => 'self_hosted',
                'price_inr'                 => 0,
                'monthly_price_inr'         => null,
                'renewal_price_inr'         => 0,
                'renewal_monthly_price_inr' => null,
                'price_2year_inr'           => null,
                'renewal_price_2year_inr'   => null,
                'price_3year_inr'           => null,
                'renewal_price_3year_inr'   => null,
                'price_usd'                 => 0,
                'monthly_price_usd'         => null,
                'renewal_price_usd'         => 0,
                'renewal_monthly_price_usd' => null,
                'price_2year_usd'           => null,
                'renewal_price_2year_usd'   => null,
                'price_3year_usd'           => null,
                'renewal_price_3year_usd'   => null,
                'billing_period'            => 'forever',
                'max_domains'               => 3,
                'max_servers'               => 1,
                'features'                  => [
                    '1 Server Node',
                    '3 Domains Limit',
                    'Automated Let\'s Encrypt SSL',
                    'Web File Manager',
                    'Database Manager & phpMyAdmin',
                    'Community Support',
                    'Basic Resource Monitoring',
                ],
                'modules'                   => ['wordpress', 'security', 'databases', 'ssl', 'file_manager', 'monitoring'],
                'is_active'                 => true,
                'is_popular'                => false,
                'cta_text'                  => 'Get Free License',
                'description'               => 'For developers, students, and hobbyists running personal servers.',
            ],

            // ==========================================
            // 2. PRO LICENSE (5 Servers)
            // ==========================================
            [
                'slug'                      => 'pro',
                'name'                      => 'Pro',
                'type'                      => 'self_hosted',
                // Monthly
                'monthly_price_inr'         => 299,
                'renewal_monthly_price_inr' => 299,
                'monthly_price_usd'         => 3,
                'renewal_monthly_price_usd' => 3,
                // 1 Year
                'price_inr'                 => 1999,
                'renewal_price_inr'         => 1999,
                'price_usd'                 => 19,
                'renewal_price_usd'         => 19,
                // 2 Years (Save 37% over monthly)
                'price_2year_inr'           => 2499,
                'renewal_price_2year_inr'   => 2499,
                'price_2year_usd'           => 34,
                'renewal_price_2year_usd'   => 34,
                // 3 Years (Save 47% over monthly)
                'price_3year_inr'           => 3199,
                'renewal_price_3year_inr'   => 3199,
                'price_3year_usd'           => 50,
                'renewal_price_3year_usd'   => 50,
                'billing_period'            => '/year',
                'max_domains'               => 50,
                'max_servers'               => 5,
                'features'                  => [
                    'Up to 5 Servers',
                    '50 Domains per Server',
                    'Git Auto-Deployments & Webhooks',
                    'WordPress Toolkit & 1-Click Staging',
                    'Fail2ban & Security Hardening',
                    'Automated Remote Cloud Backups (S3/Wasabi/FTP)',
                    'Priority Email & Community Support',
                ],
                'modules'                   => ['wordpress', 'security', 'databases', 'ssl', 'file_manager', 'monitoring', 'cron', 'backups', 'git_deploy'],
                'is_active'                 => true,
                'is_popular'                => true,
                'cta_text'                  => 'Buy Pro License',
                'description'               => 'Ideal for freelancers, studios, and expanding web platforms.',
            ],

            // ==========================================
            // 3. ENTERPRISE LICENSE (20 Servers)
            // ==========================================
            [
                'slug'                      => 'enterprise',
                'name'                      => 'Enterprise',
                'type'                      => 'self_hosted',
                // Monthly
                'monthly_price_inr'         => 349,
                'renewal_monthly_price_inr' => 349,
                'monthly_price_usd'         => 9,
                'renewal_monthly_price_usd' => 9,
                // 1 Year
                'price_inr'                 => 1999,
                'renewal_price_inr'         => 1999,
                'price_usd'                 => 49,
                'renewal_price_usd'         => 49,
                // 2 Years (Requested rate: 2999)
                'price_2year_inr'           => 2999,
                'renewal_price_2year_inr'   => 2999,
                'price_2year_usd'           => 89,
                'renewal_price_2year_usd'   => 89,
                // 3 Years (Requested rate: 3999)
                'price_3year_inr'           => 3999,
                'renewal_price_3year_inr'   => 3999,
                'price_3year_usd'           => 129,
                'renewal_price_3year_usd'   => 129,
                'billing_period'            => '/year',
                'max_domains'               => 9999,
                'max_servers'               => 20,
                'features'                  => [
                    'Up to 20 Servers',
                    'Unlimited Domains',
                    'All Nimbus Modules & Features',
                    'Supervisor Worker Daemons',
                    'Web Terminal & Shell Console',
                    'White Label Branding & Customization',
                    'Direct Sysadmin Support & SLA Guarantee',
                ],
                'modules'                   => ['wordpress', 'security', 'databases', 'cron', 'supervisor', 'file_manager', 'terminal', 'backups', 'git_deploy', 'ssl', 'emails', 'monitoring'],
                'is_active'                 => true,
                'is_popular'                => false,
                'cta_text'                  => 'Buy Enterprise License',
                'description'               => 'For hosting providers, enterprises, and high-volume agencies.',
            ],
        ];

        foreach ($nimbusPlans as $planData) {
            $plan = Plan::updateOrCreate(
                ['slug' => $planData['slug']],
                $planData
            );

            $this->command?->info("✓ Published Nimbus Plan: {$plan->name} ({$plan->slug}) - Servers: {$plan->max_servers}, Monthly: ₹{$plan->monthly_price_inr}, 1-Yr: ₹{$plan->price_inr}, 2-Yr: ₹{$plan->price_2year_inr}, 3-Yr: ₹{$plan->price_3year_inr}");
        }
    }
}
