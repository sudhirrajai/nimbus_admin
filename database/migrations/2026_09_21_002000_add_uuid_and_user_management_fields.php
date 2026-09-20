<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Invoices: add uuid
        if (Schema::hasTable('invoices') && !Schema::hasColumn('invoices', 'uuid')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->uuid('uuid')->nullable()->after('id');
            });

            // Populate existing invoices
            $invoices = DB::table('invoices')->whereNull('uuid')->get();
            foreach ($invoices as $inv) {
                DB::table('invoices')->where('id', $inv->id)->update([
                    'uuid' => (string) Str::uuid()
                ]);
            }

            Schema::table('invoices', function (Blueprint $table) {
                $table->unique('uuid');
            });
        }

        // 2. Users: add uuid, is_active, phone, company_name, notes
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'uuid')) {
                    $table->uuid('uuid')->nullable()->after('id');
                }
                if (!Schema::hasColumn('users', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('is_admin');
                }
                if (!Schema::hasColumn('users', 'phone')) {
                    $table->string('phone', 50)->nullable()->after('email');
                }
                if (!Schema::hasColumn('users', 'company_name')) {
                    $table->string('company_name', 150)->nullable()->after('phone');
                }
                if (!Schema::hasColumn('users', 'notes')) {
                    $table->text('notes')->nullable()->after('company_name');
                }
            });

            // Populate existing users
            $users = DB::table('users')->whereNull('uuid')->get();
            foreach ($users as $user) {
                DB::table('users')->where('id', $user->id)->update([
                    'uuid' => (string) Str::uuid()
                ]);
            }

            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->unique('uuid');
                });
            } catch (\Exception $e) {
                // Ignore if unique index already created
            }
        }

        // 3. Hosting Accounts: add uuid
        if (Schema::hasTable('hosting_accounts') && !Schema::hasColumn('hosting_accounts', 'uuid')) {
            Schema::table('hosting_accounts', function (Blueprint $table) {
                $table->uuid('uuid')->nullable()->after('id');
            });

            $accounts = DB::table('hosting_accounts')->whereNull('uuid')->get();
            foreach ($accounts as $acc) {
                DB::table('hosting_accounts')->where('id', $acc->id)->update([
                    'uuid' => (string) Str::uuid()
                ]);
            }

            Schema::table('hosting_accounts', function (Blueprint $table) {
                $table->unique('uuid');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'uuid')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('uuid');
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $columns = ['uuid', 'is_active', 'phone', 'company_name', 'notes'];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('users', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('hosting_accounts') && Schema::hasColumn('hosting_accounts', 'uuid')) {
            Schema::table('hosting_accounts', function (Blueprint $table) {
                $table->dropColumn('uuid');
            });
        }
    }
};
