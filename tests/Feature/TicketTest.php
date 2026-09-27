<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_view_tickets_list(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@test.com',
            'password' => bcrypt('password'),
            'is_admin' => false,
        ]);

        $response = $this->actingAs($user)->get(route('tickets.index'));
        $response->assertStatus(200);
    }

    public function test_client_can_create_ticket(): void
    {
        $user = User::create([
            'name' => 'Alice Client',
            'email' => 'alice@test.com',
            'password' => bcrypt('password'),
            'is_admin' => false,
        ]);

        $response = $this->actingAs($user)->post(route('tickets.store'), [
            'subject' => 'SSL Certificate Failed to Issue',
            'category' => 'managed_hosting',
            'priority' => 'high',
            'message' => 'Our production domain is throwing SSL handshake errors since 2pm.',
        ]);

        $this->assertDatabaseHas('tickets', [
            'user_id' => $user->id,
            'subject' => 'SSL Certificate Failed to Issue',
            'category' => 'managed_hosting',
            'priority' => 'high',
            'status' => 'open',
        ]);

        $ticket = Ticket::where('subject', 'SSL Certificate Failed to Issue')->first();
        $this->assertNotNull($ticket);
        $response->assertRedirect(route('tickets.show', ['ticket' => $ticket->id]));

        $this->assertDatabaseHas('ticket_messages', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => 'Our production domain is throwing SSL handshake errors since 2pm.',
            'is_internal' => false,
        ]);
    }

    public function test_client_can_reply_to_ticket(): void
    {
        $user = User::create([
            'name' => 'Bob Client',
            'email' => 'bob@test.com',
            'password' => bcrypt('password'),
            'is_admin' => false,
        ]);

        $ticket = Ticket::create([
            'user_id' => $user->id,
            'subject' => 'Database connection timeout',
            'category' => 'technical',
            'priority' => 'urgent',
            'status' => 'answered',
        ]);

        $response = $this->actingAs($user)->post(route('tickets.reply', ['ticket' => $ticket->id]), [
            'message' => 'Here are the MySQL error logs you requested.',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('ticket_messages', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => 'Here are the MySQL error logs you requested.',
        ]);

        $ticket->refresh();
        $this->assertEquals('open', $ticket->status); // Re-opens when client replies
    }

    public function test_unauthorized_user_cannot_view_another_clients_ticket(): void
    {
        $user1 = User::create([
            'name' => 'User One',
            'email' => 'user1@test.com',
            'password' => bcrypt('password'),
            'is_admin' => false,
        ]);

        $user2 = User::create([
            'name' => 'User Two',
            'email' => 'user2@test.com',
            'password' => bcrypt('password'),
            'is_admin' => false,
        ]);

        $ticket = Ticket::create([
            'user_id' => $user1->id,
            'subject' => 'Private Issue',
            'category' => 'billing',
            'status' => 'open',
        ]);

        $response = $this->actingAs($user2)->get(route('tickets.show', ['ticket' => $ticket->id]));
        $response->assertStatus(403);
    }

    public function test_admin_can_view_all_tickets_and_solve_issue(): void
    {
        $admin = User::create([
            'name' => 'Support Engineer',
            'email' => 'admin@vmcore.test',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $client = User::create([
            'name' => 'Client User',
            'email' => 'client@test.com',
            'password' => bcrypt('password'),
            'is_admin' => false,
        ]);

        $ticket = Ticket::create([
            'user_id' => $client->id,
            'subject' => 'Server High Memory Alert',
            'category' => 'managed_hosting',
            'priority' => 'high',
            'status' => 'open',
        ]);

        // Admin views tickets index
        $indexResponse = $this->actingAs($admin)->get(route('admin.tickets.index'));
        $indexResponse->assertStatus(200);

        // Admin views ticket detail
        $showResponse = $this->actingAs($admin)->get(route('admin.tickets.show', ['ticket' => $ticket->id]));
        $showResponse->assertStatus(200);

        // Admin adds internal staff note
        $noteResponse = $this->actingAs($admin)->post(route('admin.tickets.reply', ['ticket' => $ticket->id]), [
            'message' => 'Checked htop on node. Found runaway worker process, restarted php-fpm.',
            'is_internal' => true,
        ]);
        $noteResponse->assertSessionHas('success');

        $this->assertDatabaseHas('ticket_messages', [
            'ticket_id' => $ticket->id,
            'is_internal' => true,
            'message' => 'Checked htop on node. Found runaway worker process, restarted php-fpm.',
        ]);

        // Internal note should not be in publicMessages
        $this->assertEquals(0, $ticket->publicMessages()->count());

        // Admin replies to client and marks resolved
        $replyResponse = $this->actingAs($admin)->post(route('admin.tickets.reply', ['ticket' => $ticket->id]), [
            'message' => 'We restarted the pool and optimized OPcache. Uptime and memory are now back to normal!',
            'is_internal' => false,
            'status' => 'resolved',
        ]);
        $replyResponse->assertSessionHas('success');

        $ticket->refresh();
        $this->assertEquals('resolved', $ticket->status);
        $this->assertNotNull($ticket->resolved_at);
        $this->assertEquals(1, $ticket->publicMessages()->count());
    }
}
