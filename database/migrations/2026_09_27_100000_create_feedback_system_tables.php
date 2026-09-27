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
        Schema::create('feedback_forms', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('category')->default('managed_hosting'); // managed_hosting, self_hosted, support, general
            $table->json('questions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('allow_anonymous')->default(false);
            $table->boolean('collect_company')->default(true);
            $table->boolean('collect_role')->default(true);
            $table->string('success_title')->default('Thank you for your feedback!');
            $table->text('success_message')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('feedback_invitations', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('feedback_form_id')->constrained('feedback_forms')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('recipient_email');
            $table->string('recipient_name')->nullable();
            $table->string('token', 64)->unique();
            $table->string('status', 30)->default('sent'); // sent, completed
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('feedback_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('feedback_form_id')->constrained('feedback_forms')->cascadeOnDelete();
            $table->foreignId('feedback_invitation_id')->nullable()->constrained('feedback_invitations')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('client_name');
            $table->string('client_email');
            $table->string('client_company')->nullable();
            $table->string('client_role')->nullable();
            $table->unsignedTinyInteger('rating')->default(5);
            $table->text('feedback')->nullable();
            $table->text('suggestions')->nullable();
            $table->json('answers')->nullable();
            $table->foreignId('testimonial_id')->nullable()->constrained('testimonials')->nullOnDelete();
            $table->boolean('is_testimonial')->default(false);
            $table->string('status', 30)->default('new'); // new, reviewed, promoted_to_testimonial
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback_submissions');
        Schema::dropIfExists('feedback_invitations');
        Schema::dropIfExists('feedback_forms');
    }
};
