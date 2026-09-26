<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hosting_accounts', function (Blueprint $table) {
            $table->string('uptime_status')->default('unknown')->after('notes');
            $table->unsignedSmallInteger('uptime_status_code')->nullable()->after('uptime_status');
            $table->unsignedInteger('uptime_response_time_ms')->nullable()->after('uptime_status_code');
            $table->timestamp('uptime_last_checked_at')->nullable()->after('uptime_response_time_ms');
            $table->text('uptime_last_error')->nullable()->after('uptime_last_checked_at');
            $table->timestamp('uptime_last_alert_at')->nullable()->after('uptime_last_error');
        });

        Schema::create('hosting_account_uptime_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hosting_account_id')->constrained('hosting_accounts')->cascadeOnDelete();
            $table->string('status'); // 'up' or 'down'
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hosting_account_uptime_logs');

        Schema::table('hosting_accounts', function (Blueprint $table) {
            $table->dropColumn([
                'uptime_status',
                'uptime_status_code',
                'uptime_response_time_ms',
                'uptime_last_checked_at',
                'uptime_last_error',
                'uptime_last_alert_at',
            ]);
        });
    }
};
