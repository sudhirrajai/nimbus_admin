<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->json('modules')->nullable()->after('features');
        });

        Schema::table('licenses', function (Blueprint $table) {
            $table->json('feature_overrides')->nullable()->after('plan');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('modules');
        });

        Schema::table('licenses', function (Blueprint $table) {
            $table->dropColumn('feature_overrides');
        });
    }
};
