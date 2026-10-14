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

    public function test_multi_server_activation_allows_concurrent_servers_for_enterprise(): void
    {
        $user = User::factory()->create();
        $license = License::create([
            'user_id' => $user->id,
            'license_key' => 'ENT-MULTISERVER1',
            'plan' => 'enterprise',
            'status' => 'active',
        ]);

        // Server 1 activation
        $response1 = $this->postJson('/api/v1/verify', [
            'license_key' => $license->license_key,
            'server_ip' => '1.1.1.1',
            'machine_id' => 'mach-node-1',
            'domain' => 'node1.example.com',
        ]);
        $response1->assertOk();
        $response1->assertJson(['status' => true]);

        // Server 2 activation (same key, different machine)
        $response2 = $this->postJson('/api/v1/verify', [
            'license_key' => $license->license_key,
            'server_ip' => '2.2.2.2',
            'machine_id' => 'mach-node-2',
            'domain' => 'node2.example.com',
        ]);
        $response2->assertOk();
        $response2->assertJson(['status' => true]);

        $this->assertEquals(2, $license->activations()->count());
        $this->assertDatabaseHas('license_activations', ['license_id' => $license->id, 'machine_id' => 'mach-node-1']);
        $this->assertDatabaseHas('license_activations', ['license_id' => $license->id, 'machine_id' => 'mach-node-2']);
    }

    public function test_activation_limit_enforced_dynamically(): void
    {
        $user = User::factory()->create();
        $license = License::create([
            'user_id' => $user->id,
            'license_key' => 'PRO-LIMIT-TEST',
            'plan' => 'pro',
            'max_servers' => 2, // Admin set custom limit to 2
            'status' => 'active',
        ]);

        // Node 1: OK
        $this->postJson('/api/v1/verify', [
            'license_key' => $license->license_key,
            'server_ip' => '10.0.0.1',
            'machine_id' => 'mach-1',
        ])->assertOk();

        // Node 2: OK
        $this->postJson('/api/v1/verify', [
            'license_key' => $license->license_key,
            'server_ip' => '10.0.0.2',
            'machine_id' => 'mach-2',
        ])->assertOk();

        // Node 3: Rejects because max_servers = 2
        $response3 = $this->postJson('/api/v1/verify', [
            'license_key' => $license->license_key,
            'server_ip' => '10.0.0.3',
            'machine_id' => 'mach-3',
        ]);
        $response3->assertStatus(403);
        $response3->assertJson(['status' => false]);
        $this->assertStringContainsString('Activation limit reached', $response3->json('message'));

        // Disconnect Node 1
        $activation1 = $license->activations()->where('machine_id', 'mach-1')->first();
        $this->actingAs($user)->post("/activations/{$activation1->id}/disconnect")->assertRedirect();

        // Node 3 can now register successfully!
        $this->postJson('/api/v1/verify', [
            'license_key' => $license->license_key,
            'server_ip' => '10.0.0.3',
            'machine_id' => 'mach-3',
        ])->assertOk();

        $this->assertEquals(2, $license->activations()->count());
        $this->assertDatabaseHas('license_activations', ['license_id' => $license->id, 'machine_id' => 'mach-3']);
    }

    public function test_admin_can_disconnect_activation_and_override_max_servers(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $license = License::create([
            'user_id' => $user->id,
            'license_key' => 'ADMIN-OVERRIDE',
            'plan' => 'free',
            'status' => 'active',
        ]);

        // Default free plan has max_servers = 1
        $this->assertEquals(1, $license->getMaxServers());

        // Admin updates max_servers to 10
        $response = $this->actingAs($admin)->patch("/admin/licenses/{$license->id}", [
            'status' => 'active',
            'plan' => 'free',
            'max_servers' => 10,
        ]);
        $response->assertRedirect();

        $license->refresh();
        $this->assertEquals(10, $license->max_servers);
        $this->assertEquals(10, $license->getMaxServers());

        // Now multiple servers can register under this free license
        $this->postJson('/api/v1/verify', [
            'license_key' => $license->license_key,
            'server_ip' => '1.1.1.1',
            'machine_id' => 'node-a',
        ])->assertOk();

        $this->postJson('/api/v1/verify', [
            'license_key' => $license->license_key,
            'server_ip' => '2.2.2.2',
            'machine_id' => 'node-b',
        ])->assertOk();

        $this->assertEquals(2, $license->activations()->count());

        // Admin disconnects node-a
        $nodeA = $license->activations()->where('machine_id', 'node-a')->first();
        $this->actingAs($admin)
            ->post("/admin/licenses/activations/{$nodeA->id}/disconnect")
            ->assertRedirect();

        $this->assertEquals(1, $license->activations()->count());
        $this->assertDatabaseMissing('license_activations', ['machine_id' => 'node-a']);
    }
}
