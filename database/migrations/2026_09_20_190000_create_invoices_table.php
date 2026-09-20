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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_number')->unique();
            $table->string('type')->default('license_plan'); // license_plan, hosting_plan, custom
            $table->string('plan_name')->nullable(); // e.g. Pro Plan, Managed Cloud VPS
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->string('currency', 10)->default('INR');
            $table->enum('status', ['paid', 'pending', 'cancelled'])->default('paid');
            $table->string('payment_method')->default('Razorpay'); // Razorpay, Admin Assignment, Bank Transfer, Complimentary
            $table->string('payment_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('billing_details')->nullable(); // Customer name, email, domain, server, notes
            $table->foreignId('license_id')->nullable()->constrained('licenses')->nullOnDelete();
            $table->foreignId('hosting_account_id')->nullable()->constrained('hosting_accounts')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
