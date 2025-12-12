<?php

namespace Tests\Feature\Repositories;

use App\Repositories\EloquentTicketRepository;
use App\Models\SupportedTicket;
use App\Models\SupportedTicketMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EloquentTicketRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentTicketRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new EloquentTicketRepository();
    }

    /** @test */
    public function it_creates_ticket()
    {
        $user = User::factory()->create();
        $data = [
            'customer_id' => $user->id,
            'title' => 'Test Ticket',
            'message' => 'Initial message',
            'status' => 'pending',
            'priority' => 'normal',
        ];

        $ticket = $this->repo->createTicket($data);

        $this->assertInstanceOf(SupportedTicket::class, $ticket);
        $this->assertDatabaseHas('supported_tickets', ['title' => 'Test Ticket']);
    }

    /** @test */
    public function it_finds_ticket_by_id_with_relations()
    {
        $user = User::factory()->create();
        $ticket = SupportedTicket::factory()->create(['customer_id' => $user->id]);
        $message = SupportedTicketMessage::factory()->create([
            'supported_ticket_id' => $ticket->id,
            'sender_id' => $user->id,
        ]);

        $found = $this->repo->findTicketById($ticket->id);

        $this->assertEquals($ticket->id, $found->id);
        $this->assertEquals($user->id, $found->customer->id);
        $this->assertTrue($found->children->contains(fn($m) => $m->id === $message->id));
    }

    /** @test */
    public function it_lists_user_tickets()
    {
        $user = User::factory()->create();
        $ticket1 = SupportedTicket::factory()->create(['customer_id' => $user->id]);
        $ticket2 = SupportedTicket::factory()->create(['customer_id' => $user->id]);

        $result = $this->repo->listUserTickets($user->id);

        $this->assertCount(2, $result);
        $this->assertEquals($user->id, $result[0]->customer_id);
    }

    /** @test */
    public function it_lists_all_tickets_with_filters()
    {
        SupportedTicket::factory()->create(['status' => 'pending', 'priority' => 'normal']);
        SupportedTicket::factory()->create(['status' => 'closed', 'priority' => 'high']);

        $resultStatus = $this->repo->listAllTickets(['status' => 'closed']);
        $this->assertCount(1, $resultStatus);
        $this->assertEquals('closed', $resultStatus[0]->status);

        $resultPriority = $this->repo->listAllTickets(['priority' => 'high']);
        $this->assertCount(1, $resultPriority);
        $this->assertEquals('high', $resultPriority[0]->priority);
    }

    /** @test */
    public function it_adds_message_to_ticket()
    {
        $user = User::factory()->create();
        $ticket = SupportedTicket::factory()->create(['customer_id' => $user->id]);

        $message = $this->repo->addMessage($ticket->id, [
            'sender_id' => $user->id,
            'sender_type' => 'user',
            'message' => 'Reply message',
            'is_private' => false,
        ]);

        $this->assertInstanceOf(SupportedTicketMessage::class, $message);
        $this->assertDatabaseHas('supported_ticket_messages', ['message' => 'Reply message']);
    }

    /** @test */
    public function it_gets_messages_for_ticket()
    {
        $ticket = SupportedTicket::factory()->create();
        $msg1 = SupportedTicketMessage::factory()->create(['supported_ticket_id' => $ticket->id, 'message' => 'First']);
        $msg2 = SupportedTicketMessage::factory()->create(['supported_ticket_id' => $ticket->id, 'message' => 'Second']);

        $messages = $this->repo->getMessages($ticket->id);

        $this->assertCount(2, $messages);
        $this->assertEquals('First', $messages[0]->message);
        $this->assertEquals('Second', $messages[1]->message);
    }

    /** @test */
    public function it_updates_ticket()
    {
        $ticket = SupportedTicket::factory()->create(['status' => 'pending']);
        $updated = $this->repo->updateTicket($ticket->id, ['status' => 'closed']);

        $this->assertEquals('closed', $updated->status);
        $this->assertDatabaseHas('supported_tickets', ['id' => $ticket->id, 'status' => 'closed']);
    }
}