<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\License;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_claim_free_license(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/licenses/free');

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('licenses', [
            'user_id' => $user->id,
            'plan' => 'free',
            'status' => 'active',
        ]);
    }

    public function test_user_cannot_claim_multiple_active_free_licenses(): void
    {
        $user = User::factory()->create();

        // Claim first free license
        $this->actingAs($user)->post('/licenses/free');

        // Try claiming second free license
        $response = $this
            ->actingAs($user)
            ->post('/licenses/free');

        $response->assertRedirect();
        $response->assertSessionHasErrors(['error']);

        // Assert only one license exists
        $this->assertEquals(1, License::where('user_id', $user->id)->count());
    }

    public function test_user_can_disconnect_machine_installation(): void
    {
        $user = User::factory()->create();
        $license = License::create([
            'user_id' => $user->id,
            'license_key' => 'FREE-TEST1234',
            'plan' => 'free',
            'status' => 'active',
            'server_ip' => '192.168.1.1',
            'machine_id' => 'mach-1234',
            'domain' => 'example.com',
            'admin_name' => 'Admin User',
            'admin_email' => 'admin@example.com',
        ]);

        $response = $this
            ->actingAs($user)
            ->post("/licenses/{$license->id}/disconnect");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $license->refresh();
        $this->assertNull($license->server_ip);
        $this->assertNull($license->machine_id);
        $this->assertNull($license->domain);
        $this->assertNull($license->admin_name);
        $this->assertNull($license->admin_email);
    }

    public function test_user_can_revoke_license_and_then_claim_new_one(): void
    {
        $user = User::factory()->create();
        $license = License::create([
            'user_id' => $user->id,
            'license_key' => 'FREE-TEST1234',
            'plan' => 'free',
            'status' => 'active',
        ]);

        // Revoke license
        $response = $this
            ->actingAs($user)
            ->post("/licenses/{$license->id}/revoke");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $license->refresh();
        $this->assertSame('revoked', $license->status);

        // Try claiming new free license (should succeed now)
        $responseClaim = $this
            ->actingAs($user)
            ->post('/licenses/free');

        $responseClaim->assertRedirect();
        $responseClaim->assertSessionHas('success');

        // Total of 2 licenses now: 1 revoked, 1 active
        $this->assertEquals(2, License::where('user_id', $user->id)->count());
        $this->assertEquals(1, License::where('user_id', $user->id)->where('status', 'active')->count());
    }

    public function test_user_cannot_disconnect_or_revoke_other_users_license(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $license = License::create([
            'user_id' => $user1->id,
            'license_key' => 'FREE-TEST1234',
            'plan' => 'free',
            'status' => 'active',
        ]);

        // Try disconnecting other user's license
        $responseDisconnect = $this
            ->actingAs($user2)
            ->post("/licenses/{$license->id}/disconnect");

        $responseDisconnect->assertStatus(403);

        // Try revoking other user's license
        $responseRevoke = $this
            ->actingAs($user2)
            ->post("/licenses/{$license->id}/revoke");

        $responseRevoke->assertStatus(403);
    }
}
