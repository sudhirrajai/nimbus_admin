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
        Schema::create('license_activations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->constrained('licenses')->onDelete('cascade');
            $table->string('machine_id');
            $table->string('server_ip');
            $table->string('domain')->nullable();
            $table->string('admin_name')->nullable();
            $table->string('admin_email')->nullable();
            $table->timestamp('last_heartbeat_at')->nullable();
            $table->timestamps();

            $table->unique(['license_id', 'machine_id']);
            $table->index('machine_id');
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->integer('max_servers')->default(1)->after('max_domains');
        });

        Schema::table('licenses', function (Blueprint $table) {
            $table->integer('max_servers')->nullable()->after('plan');
        });

        // Set default max_servers on plans
        DB::table('plans')->where('slug', 'free')->update(['max_servers' => 1]);
        DB::table('plans')->where('slug', 'pro')->update(['max_servers' => 5]);
        DB::table('plans')->where('slug', 'enterprise')->update(['max_servers' => 9999]);

        // Backfill existing connected machines from licenses table into license_activations
        $existing = DB::table('licenses')
            ->whereNotNull('machine_id')
            ->where('machine_id', '!=', '')
            ->get();

        foreach ($existing as $lic) {
            DB::table('license_activations')->insertOrIgnore([
                'license_id'        => $lic->id,
                'machine_id'        => $lic->machine_id,
                'server_ip'         => $lic->server_ip ?? '127.0.0.1',
                'domain'            => $lic->domain,
                'admin_name'        => $lic->admin_name,
                'admin_email'       => $lic->admin_email,
                'last_heartbeat_at' => $lic->last_heartbeat_at ?? $lic->last_checked_at,
                'created_at'        => $lic->created_at ?? now(),
                'updated_at'        => $lic->updated_at ?? now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_activations');

        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('max_servers');
        });

        Schema::table('licenses', function (Blueprint $table) {
            $table->dropColumn('max_servers');
        });
    }
};
