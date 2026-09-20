<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('hosting_accounts', function (Blueprint $table) {
            $table->string('billing_cycle')->default('yearly')->after('package_name'); // monthly, quarterly, semi_annual, yearly, biennial
            $table->decimal('initial_price', 10, 2)->nullable()->after('billing_cycle'); // e.g. 3800.00
            $table->decimal('renewal_price', 10, 2)->nullable()->after('initial_price'); // e.g. 4790.00
            $table->string('currency', 10)->default('INR')->after('renewal_price');
            $table->timestamp('renews_at')->nullable()->after('currency'); // Next renewal due date
            $table->boolean('auto_invoice')->default(true)->after('renews_at'); // Automatically generate invoice before renewal
            $table->integer('renewal_invoice_days')->default(14)->after('auto_invoice'); // Days before renewal to generate invoice
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->integer('renewal_price_inr')->nullable()->after('price_inr');
            $table->integer('renewal_price_usd')->nullable()->after('price_usd');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->timestamp('due_date')->nullable()->after('paid_at');
            $table->timestamp('period_start')->nullable()->after('due_date');
            $table->timestamp('period_end')->nullable()->after('period_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hosting_accounts', function (Blueprint $table) {
            $table->dropColumn([
                'billing_cycle',
                'initial_price',
                'renewal_price',
                'currency',
                'renews_at',
                'auto_invoice',
                'renewal_invoice_days',
            ]);
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'renewal_price_inr',
                'renewal_price_usd',
            ]);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'due_date',
                'period_start',
                'period_end',
            ]);
        });
    }
};
