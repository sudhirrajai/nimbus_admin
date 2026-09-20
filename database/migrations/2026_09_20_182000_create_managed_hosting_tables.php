<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hosting_servers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "Primary Nimbus Node 01"
            $table->string('hostname')->nullable(); // e.g. "srv1.vmcore.in"
            $table->string('ip_address'); // e.g. "66.116.204.19"
            $table->string('nimbus_url'); // e.g. "https://nimbus.vmcore.in"
            $table->string('api_secret'); // Secure random shared token for SSO and API
            $table->enum('status', ['active', 'maintenance', 'offline'])->default('active');
            $table->string('location')->nullable(); // e.g. "Mumbai, India"
            $table->json('specs')->nullable(); // e.g. {"ram": "16GB", "cpu": "8 vCPU", "storage": "200GB NVMe"}
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('hosting_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('server_id')->constrained('hosting_servers')->cascadeOnDelete();
            $table->string('primary_domain'); // e.g. "clientbrand.com"
            $table->string('username')->nullable(); // site username in Nimbus e.g. "site_clientbrand"
            $table->string('package_name')->default('Managed Cloud');
            $table->enum('status', ['active', 'suspended', 'pending'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('hosting_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('domain')->nullable();
            $table->string('plan_requested')->default('managed_standard');
            $table->text('requirements')->nullable();
            $table->enum('status', ['pending', 'contacted', 'approved', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hosting_accounts');
        Schema::dropIfExists('hosting_requests');
        Schema::dropIfExists('hosting_servers');
    }
};
