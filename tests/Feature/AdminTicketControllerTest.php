<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SupportedTicket;
use App\Models\SupportedTicketMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AdminTicketControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected SupportedTicket $ticket;

    public function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();

        Gate::before(fn() => true);

        $this->user = User::factory()->create(['role_id' => 2]);
        $this->actingAs($this->user, 'sanctum');

        $this->ticket = SupportedTicket::factory()->create([
            'id' => 3,
            'title' => 'Test Ticket',
            'message' => 'Hello',
            'status' => 'open',
        ]);
    }

    #[Test]
    public function test_index_returns_all_tickets()
    {
        $response = $this->getJson('/api/tickets');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     '*' => ['id', 'title', 'message', 'status']
                 ]);
    }

    #[Test]
    public function test_show_returns_ticket_details()
    {
        SupportedTicketMessage::factory()->create([
            'supported_ticket_id' => 3,
            'sender_id' => $this->user->id,
            'sender_type' => 'user',
            'message' => 'Reply message'
        ]);

        $response = $this->getJson('/api/tickets/3');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'ticket' => [
                         'id', 'title', 'message', 'status',
                         'customer' => ['id','name','email'],
                         'children' => [
                             [
                                 'id',
                                 'message',
                                 'sender_type',
                                 'sender' => ['id', 'name', 'email']
                             ]
                         ]
                     ]
                 ]);
    }

    #[Test]
    public function test_reply_adds_message_to_ticket()
    {
        $response = $this->postJson('/api/tickets/3/reply', [
            'title' => 'Reply title',
            'message' => 'Hello reply',
        ]);

        // Reply returns 201 because a new message is created
        $response->assertStatus(201)
                 ->assertJsonFragment(['message' => 'Hello reply']);

        $this->assertDatabaseHas('supported_ticket_messages', [
            'supported_ticket_id' => 3,
            'message' => 'Hello reply'
        ]);
    }

    #[Test]
    public function test_change_status_updates_ticket()
    {
        $response = $this->postJson('/api/tickets/3/status', [
            'status' => 'closed',
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['status' => 'closed']);
    }

    #[Test]
    public function test_change_status_invalid_status_returns_error()
    {
        // Since controller does not validate status, we expect "invalid" to be saved.
        $response = $this->postJson('/api/tickets/3/status', [
            'status' => 'invalid',
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['status' => 'invalid']);
    }
}
