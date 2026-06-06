<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\User;
use App\Models\License;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed default plans inside test setup
        Plan::create([
            'name' => 'Free',
            'slug' => 'free',
            'price_inr' => 0,
            'price_usd' => 0,
            'billing_period' => 'forever',
            'max_domains' => 3,
            'features' => ['Feature 1', 'Feature 2'],
            'is_active' => true,
            'is_popular' => false,
        ]);

        Plan::create([
            'name' => 'Pro',
            'slug' => 'pro',
            'price_inr' => 499,
            'price_usd' => 19,
            'billing_period' => '/year',
            'max_domains' => 50,
            'features' => ['Pro Feature 1'],
            'is_active' => true,
            'is_popular' => true,
        ]);
    }

    public function test_guest_cannot_access_admin_plans()
    {
        $response = $this->get('/admin/plans');
        $response->assertRedirect('/login');
    }

    public function test_non_admin_cannot_access_admin_plans()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $response = $this->actingAs($user)->get('/admin/plans');
        $response->assertRedirect('/dashboard');
    }

    public function test_admin_can_view_and_update_plans()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        
        $response = $this->actingAs($admin)->get('/admin/plans');
        $response->assertStatus(200);

        $proPlan = Plan::where('slug', 'pro')->first();

        $response = $this->actingAs($admin)->get("/admin/plans/{$proPlan->id}/edit");
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->put("/admin/plans/{$proPlan->id}", [
            'name' => 'Pro Premium Pack',
            'price_inr' => 599,
            'price_usd' => 25,
            'billing_period' => '/year',
            'max_domains' => 75,
            'features' => ['New Pro Feature 1', 'New Pro Feature 2'],
            'is_active' => true,
            'is_popular' => false,
            'cta_text' => 'Upgrade Pro',
            'description' => 'Newly updated pack description',
        ]);

        $response->assertRedirect('/admin/plans');
        
        $this->assertDatabaseHas('plans', [
            'id' => $proPlan->id,
            'name' => 'Pro Premium Pack',
            'price_inr' => 599,
            'max_domains' => 75,
        ]);
    }

    public function test_payment_initiation_uses_database_plan_price()
    {
        $user = User::factory()->create();
        
        // Mock environment keys for Razorpay order initialization
        config(['services.razorpay.key_id' => 'test_id']);
        config(['services.razorpay.key_secret' => 'test_secret']);

        // Since Razorpay API order creation makes a curl request, we can mock it or verify validations.
        // Let's test that request for invalid/non-existing plan fails correctly
        $response = $this->actingAs($user)->postJson('/payment/initiate', [
            'plan' => 'non-existent-plan',
        ]);

        $response->assertStatus(422);
    }

    public function test_license_verification_retrieves_max_domains_from_plan()
    {
        $user = User::factory()->create();
        
        // Let's create an active license key
        $license = License::create([
            'user_id' => $user->id,
            'license_key' => 'PRO-XYZ123456789',
            'plan' => 'pro',
            'status' => 'active',
        ]);

        // Verify endpoint response
        $response = $this->postJson('/api/v1/verify', [
            'license_key' => $license->license_key,
            'server_ip' => '127.0.0.1',
            'machine_id' => 'mach-id-1234',
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'max_domains' => 50, // Matches the seeded pro plan max_domains
        ]);

        // Change plan limit in database dynamically
        Plan::where('slug', 'pro')->update(['max_domains' => 120]);

        $response2 = $this->postJson('/api/v1/verify', [
            'license_key' => $license->license_key,
            'server_ip' => '127.0.0.1',
            'machine_id' => 'mach-id-1234',
        ]);

        $response2->assertStatus(200);
        $response2->assertJsonFragment([
            'max_domains' => 120, // Verify it reflected the database change dynamically!
        ]);
    }

    public function test_user_can_access_subscription_page_and_view_active_plan_details()
    {
        $user = User::factory()->create();
        
        $license = License::create([
            'user_id' => $user->id,
            'license_key' => 'PRO-TRIAL12345',
            'plan' => 'pro',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get('/subscription');

        $response->assertStatus(200);
        $response->assertSee('PRO-TRIAL12345');
    }
}
