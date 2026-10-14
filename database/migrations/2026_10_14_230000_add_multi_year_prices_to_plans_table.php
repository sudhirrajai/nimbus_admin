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
            $table->decimal('price_2year_inr', 10, 2)->nullable()->after('renewal_monthly_price_inr');
            $table->decimal('price_2year_usd', 10, 2)->nullable()->after('price_2year_inr');
            $table->decimal('renewal_price_2year_inr', 10, 2)->nullable()->after('price_2year_usd');
            $table->decimal('renewal_price_2year_usd', 10, 2)->nullable()->after('renewal_price_2year_inr');

            $table->decimal('price_3year_inr', 10, 2)->nullable()->after('renewal_price_2year_usd');
            $table->decimal('price_3year_usd', 10, 2)->nullable()->after('price_3year_inr');
            $table->decimal('renewal_price_3year_inr', 10, 2)->nullable()->after('price_3year_usd');
            $table->decimal('renewal_price_3year_usd', 10, 2)->nullable()->after('renewal_price_3year_inr');
        });

        // Set initial multi-year prices for self-hosted Nimbus plans
        DB::table('plans')->where('slug', 'pro')->update([
            'price_2year_inr' => 899,
            'price_2year_usd' => 35,
            'renewal_price_2year_inr' => 899,
            'renewal_price_2year_usd' => 35,
            'price_3year_inr' => 1299,
            'price_3year_usd' => 49,
            'renewal_price_3year_inr' => 1299,
            'renewal_price_3year_usd' => 49,
        ]);

        DB::table('plans')->where('slug', 'enterprise')->update([
            'price_2year_inr' => 3599,
            'price_2year_usd' => 89,
            'renewal_price_2year_inr' => 3599,
            'renewal_price_2year_usd' => 89,
            'price_3year_inr' => 4999,
            'price_3year_usd' => 129,
            'renewal_price_3year_inr' => 4999,
            'renewal_price_3year_usd' => 129,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'price_2year_inr',
                'price_2year_usd',
                'renewal_price_2year_inr',
                'renewal_price_2year_usd',
                'price_3year_inr',
                'price_3year_usd',
                'renewal_price_3year_inr',
                'renewal_price_3year_usd',
            ]);
        });
    }
};
