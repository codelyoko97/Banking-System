<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SupportedTicket;
use App\Models\SupportedTicketMessage;
use App\Services\TicketService;
use Illuminate\Auth\Access\Response as AuthResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class TicketControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $ticketService;

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();

        // إنشاء مستخدم تجريبي
        $user = User::factory()->create(['role_id' => 2]);
        $this->actingAs($user);

        // تجاوز الـ authorize
        Gate::shouldReceive('authorize')->andReturn(AuthResponse::allow());
        Gate::shouldReceive('allows')->andReturn(true);
        Gate::shouldReceive('forUser')->andReturnSelf();
        Gate::shouldReceive('check')->andReturn(true);

        // Mock TicketService
        $this->ticketService = Mockery::mock(TicketService::class);
        $this->app->instance(TicketService::class, $this->ticketService);
    }

    #[Test]
    public function test_store_creates_ticket()
    {
        $payload = [
            'title' => 'Login Issue',
            'message' => 'I cannot login to my account.',
        ];

        $created = new SupportedTicket([
            'id' => 1,
            'title' => 'Login Issue',
            'message' => 'I cannot login to my account.',
            'customer_id' => auth()->id(),
            'status' => 'open',
        ]);

        $this->ticketService->shouldReceive('createTicket')
            ->once()
            ->andReturn($created);

        $response = $this->postJson('/api/tickets', $payload);

        $response->assertStatus(201)
                 ->assertJsonFragment(['title' => 'Login Issue']);
    }

    #[Test]
    public function test_index_returns_all_tickets_for_staff()
    {
        $staff = User::factory()->create(['role_id' => 5]);
        $this->actingAs($staff);

        $tickets = [
            new SupportedTicket(['id' => 1, 'title' => 'Test ticket', 'message' => 'Subject A']),
            new SupportedTicket(['id' => 2, 'title' => 'Another ticket', 'message' => 'Subject B']),
        ];

        $this->ticketService->shouldReceive('listAllTickets')
            ->once()
            ->andReturn($tickets);

        $response = $this->getJson('/api/tickets');

        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => 'Test ticket']);
    }

    #[Test]
    public function test_index_returns_user_tickets()
    {
        $tickets = [
            new SupportedTicket([
                'id' => 1,
                'title' => 'My ticket',
                'message' => 'Subject C',
                'customer_id' => auth()->id(),
            ]),
        ];

        $this->ticketService->shouldReceive('getUserTickets')
            ->with(auth()->id())
            ->once()
            ->andReturn($tickets);

        $response = $this->getJson('/api/tickets');

        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => 'My ticket']);
    }

    #[Test]
    public function test_show_ticket()
    {
        $ticket = new SupportedTicket([
            'id' => 1,
            'title' => 'Test title',
            'message' => 'Problem with account',
            'status' => 'open',
        ]);

        $this->ticketService->shouldReceive('getTicket')
            ->with(1)
            ->once()
            ->andReturn($ticket);

        $response = $this->getJson('/api/tickets/1');

        $response->assertStatus(200)
                 ->assertJson(['ticket' => $ticket->toArray()]);
    }

    #[Test]
    public function test_reply_to_ticket_as_user()
    {
        $payload = [
            'title' => 'Re: Login Issue',
            'message' => 'We are checking your issue.',
        ];

        $ticket = new SupportedTicket(['id' => 1, 'title' => 'Login Issue', 'status' => 'open']);

        $reply = new SupportedTicketMessage([
            'id' => 1,
            'ticket_id' => 1,
            'sender_type' => 'user',
            'message' => 'We are checking your issue.',
        ]);

        $this->ticketService->shouldReceive('getTicket')->with(1)->once()->andReturn($ticket);
        $this->ticketService->shouldReceive('replyTicket')->with(1, Mockery::type('array'))->once()->andReturn($reply);

        $response = $this->postJson('/api/tickets/1/reply', $payload);

        $response->assertStatus(201)
                 ->assertJsonFragment(['sender_type' => 'user']);
    }

    #[Test]
    public function test_reply_to_ticket_as_staff()
    {
        $staff = User::factory()->create(['role_id' => 5]);
        $this->actingAs($staff);

        $payload = [
            'title' => 'Re: Staff Reply',
            'message' => 'Staff reply message.',
        ];

        $ticket = new SupportedTicket(['id' => 1, 'title' => 'Staff Reply Title', 'status' => 'open']);

        $reply = new SupportedTicketMessage([
            'id' => 1,
            'ticket_id' => 1,
            'sender_type' => 'staff',
            'message' => 'Staff reply message.',
        ]);

        $this->ticketService->shouldReceive('getTicket')->with(1)->once()->andReturn($ticket);
        $this->ticketService->shouldReceive('replyTicket')->with(1, Mockery::type('array'))->once()->andReturn($reply);

        $response = $this->postJson('/api/tickets/1/reply', $payload);

        $response->assertStatus(201)
                 ->assertJsonFragment(['sender_type' => 'staff']);
    }

    #[Test]
    public function test_change_status()
    {
        $ticket = new SupportedTicket([
            'id' => 1,
            'title' => 'Status Change',
            'message' => 'Subject D',
            'status' => 'closed',
        ]);

        $this->ticketService->shouldReceive('changeStatus')
            ->with(1, 'closed')
            ->once()
            ->andReturn($ticket);

        $response = $this->postJson('/api/tickets/1/status', ['status' => 'closed']);

        $response->assertStatus(200)
                 ->assertJsonFragment(['status' => 'closed']);
    }
}