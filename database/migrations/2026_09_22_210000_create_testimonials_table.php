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
        if (!Schema::hasTable('testimonials')) {
            Schema::create('testimonials', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('role')->nullable();
                $table->string('company')->nullable();
                $table->string('location')->nullable();
                $table->text('quote');
                $table->unsignedTinyInteger('rating')->default(5);
                $table->string('avatar')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });

            // Seed initial client testimonials
            DB::table('testimonials')->insert([
                [
                    'name' => 'TestMe',
                    'role' => 'Managed Cloud Client',
                    'company' => 'TestMe',
                    'location' => 'Mumbai, Maharashtra',
                    'quote' => 'Best managed hosting service we have used. Got instant support, rock-solid 99.9% uptime, and direct sysadmin care that lets us focus entirely on our business.',
                    'rating' => 5,
                    'is_active' => true,
                    'sort_order' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'dmanindia',
                    'role' => 'E-Commerce Website',
                    'company' => 'dmanindia',
                    'location' => 'Vapi, Gujarat, India',
                    'quote' => 'Running an e-commerce website requires high speed and uninterrupted availability. Nimbus handles our customer traffic spikes and flash sales with effortless stability.',
                    'rating' => 5,
                    'is_active' => true,
                    'sort_order' => 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Maharaj POS',
                    'role' => 'Retail Point of Sale Systems',
                    'company' => 'Maharaj POS',
                    'location' => 'Panchmahal, Gujarat, India',
                    'quote' => 'Our point-of-sale retail clients demand 24/7 reliability. Hosting on Nimbus fully managed cloud gave us instant response times, automated backups, and complete peace of mind.',
                    'rating' => 5,
                    'is_active' => true,
                    'sort_order' => 3,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
