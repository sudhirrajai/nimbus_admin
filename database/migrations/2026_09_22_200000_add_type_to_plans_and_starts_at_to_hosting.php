<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add 'type' to plans table ('self_hosted' vs 'managed_hosting')
        if (Schema::hasTable('plans') && !Schema::hasColumn('plans', 'type')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->string('type', 50)->default('self_hosted')->after('slug')->index();
            });
        }

        // 2. Add 'starts_at' to hosting_accounts table
        if (Schema::hasTable('hosting_accounts') && !Schema::hasColumn('hosting_accounts', 'starts_at')) {
            Schema::table('hosting_accounts', function (Blueprint $table) {
                $table->timestamp('starts_at')->nullable()->after('billing_cycle');
            });

            // Backfill existing hosting accounts starts_at with created_at
            DB::table('hosting_accounts')
                ->whereNull('starts_at')
                ->update(['starts_at' => DB::raw('created_at')]);
        }

        // 3. Seed default managed hosting plans if they don't already exist
        if (Schema::hasTable('plans')) {
            $defaultManagedPlans = [
                [
                    'name' => 'Starter Cloud',
                    'slug' => 'starter-cloud',
                    'type' => 'managed_hosting',
                    'price_inr' => 3800,
                    'renewal_price_inr' => 4790,
                    'price_usd' => 49,
                    'renewal_price_usd' => 59,
                    'billing_period' => '/year',
                    'max_domains' => 5,
                    'features' => json_encode([
                        '1 vCPU & 2GB RAM Cloud Node',
                        '30GB NVMe High-Speed Storage',
                        'Fully Managed by VMCORE Team',
                        'Free Auto-Renewing SSL',
                        'Automated Daily Backups',
                        'Standard Server Monitoring',
                    ]),
                    'modules' => json_encode(['wordpress', 'security', 'databases', 'ssl', 'backups']),
                    'is_active' => true,
                    'is_popular' => false,
                    'cta_text' => 'Get Managed Starter',
                    'description' => 'Perfect for personal sites, blogs, and light production workloads.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Business Cloud',
                    'slug' => 'business-cloud',
                    'type' => 'managed_hosting',
                    'price_inr' => 7990,
                    'renewal_price_inr' => 9990,
                    'price_usd' => 99,
                    'renewal_price_usd' => 119,
                    'billing_period' => '/year',
                    'max_domains' => 25,
                    'features' => json_encode([
                        '2 vCPU & 4GB RAM Dedicated Cloud Node',
                        '80GB NVMe Enterprise Storage',
                        'Fully Managed by Nimbus Engineers',
                        'Priority 24/7 Operations Support',
                        'Fail2ban & Advanced DDoS Shield',
                        'Automated Hourly / Daily Backups',
                        'Staging Environments & Git Deploy',
                    ]),
                    'modules' => json_encode(['wordpress', 'security', 'databases', 'ssl', 'backups', 'git_deploy', 'monitoring', 'cron']),
                    'is_active' => true,
                    'is_popular' => true,
                    'cta_text' => 'Deploy Business Cloud',
                    'description' => 'High-performance cloud for e-commerce, agencies, and high-traffic sites.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Enterprise Cloud',
                    'slug' => 'enterprise-cloud',
                    'type' => 'managed_hosting',
                    'price_inr' => 16990,
                    'renewal_price_inr' => 19990,
                    'price_usd' => 199,
                    'renewal_price_usd' => 249,
                    'billing_period' => '/year',
                    'max_domains' => 100,
                    'features' => json_encode([
                        '4 vCPU & 8GB RAM High-Performance Node',
                        '160GB NVMe Extreme Storage',
                        'Dedicated VMCORE SysAdmin Support',
                        'Custom Nginx & PHP Optimization',
                        'White Glove Migration Included',
                        'Real-time Uptime SLA (99.9%)',
                        'Custom Daemon & Supervisor Management',
                    ]),
                    'modules' => json_encode(['wordpress', 'security', 'databases', 'ssl', 'backups', 'git_deploy', 'monitoring', 'cron', 'supervisor', 'terminal']),
                    'is_active' => true,
                    'is_popular' => false,
                    'cta_text' => 'Get Enterprise Cloud',
                    'description' => 'Maximum speed, dedicated isolated resources, and direct sysadmin support.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            foreach ($defaultManagedPlans as $managedPlan) {
                $exists = DB::table('plans')->where('slug', $managedPlan['slug'])->exists();
                if (!$exists) {
                    DB::table('plans')->insert($managedPlan);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('hosting_accounts') && Schema::hasColumn('hosting_accounts', 'starts_at')) {
            Schema::table('hosting_accounts', function (Blueprint $table) {
                $table->dropColumn('starts_at');
            });
        }

        if (Schema::hasTable('plans') && Schema::hasColumn('plans', 'type')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }
};
