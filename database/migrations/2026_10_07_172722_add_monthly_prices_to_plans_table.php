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
        Schema::table('plans', function (Blueprint $table) {
            $table->decimal('monthly_price_inr', 10, 2)->nullable()->after('price_inr');
            $table->decimal('monthly_price_usd', 10, 2)->nullable()->after('price_usd');
            $table->decimal('renewal_monthly_price_inr', 10, 2)->nullable()->after('renewal_price_inr');
            $table->decimal('renewal_monthly_price_usd', 10, 2)->nullable()->after('renewal_price_usd');
        });

        // Set initial monthly prices for self-hosted Nimbus plans
        DB::table('plans')->where('slug', 'pro')->update([
            'monthly_price_inr' => 49,
            'monthly_price_usd' => 2,
            'renewal_monthly_price_inr' => 49,
            'renewal_monthly_price_usd' => 2,
        ]);

        DB::table('plans')->where('slug', 'enterprise')->update([
            'monthly_price_inr' => 199,
            'monthly_price_usd' => 5,
            'renewal_monthly_price_inr' => 199,
            'renewal_monthly_price_usd' => 5,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'monthly_price_inr',
                'monthly_price_usd',
                'renewal_monthly_price_inr',
                'renewal_monthly_price_usd',
            ]);
        });
    }
};
