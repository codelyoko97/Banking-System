<?php

namespace Tests\Feature\Services;

use App\Services\TicketService;
use App\Repositories\TicketRepositoryInterface;
use App\Models\SupportedTicket;
use App\Models\SupportedTicketMessage;
use App\Events\TicketCreated;
use App\Events\TicketReplied;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;

class TicketServiceTest extends TestCase
{
    use RefreshDatabase;

    private $repoMock;
    private $service;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake(); // منع تنفيذ الأحداث الحقيقية
        $this->repoMock = Mockery::mock(TicketRepositoryInterface::class);
        $this->service = new TicketService($this->repoMock);
    }

    /** @test */
    public function it_lists_all_tickets()
    {
        $tickets = collect([new SupportedTicket(['id' => 1, 'title' => 'Test Ticket'])]);

        $this->repoMock
            ->shouldReceive('listAllTickets')
            ->once()
            ->with([])
            ->andReturn($tickets);

        $result = $this->service->listAllTickets();

        $this->assertCount(1, $result);
        $this->assertEquals('Test Ticket', $result[0]->title);
    }

    /** @test */
    public function it_creates_ticket_and_dispatches_event()
    {
        $data = [
            'customer_id' => 1,
            'title' => 'Issue',
            'message' => 'Problem description',
        ];

        $ticket = new SupportedTicket(['id' => 1, 'title' => 'Issue', 'customer_id' => 1]);
        $message = new SupportedTicketMessage(['id' => 10, 'message' => 'Problem description']);

        $this->repoMock
            ->shouldReceive('createTicket')
            ->once()
            ->andReturn($ticket);

        $this->repoMock
            ->shouldReceive('addMessage')
            ->once()
            ->with($ticket->id, Mockery::on(fn($m) => $m['message'] === 'Problem description'))
            ->andReturn($message);

        $result = $this->service->createTicket($data);

        $this->assertInstanceOf(SupportedTicket::class, $result);
        Event::assertDispatched(TicketCreated::class);
    }

    /** @test */
    public function it_replies_to_ticket_and_dispatches_event()
    {
        $ticketId = 1;
        $data = [
            'sender_id' => 2,
            'sender_type' => 'user',
            'message' => 'Reply message',
        ];

        $message = new SupportedTicketMessage(['id' => 20, 'message' => 'Reply message']);

        $this->repoMock
            ->shouldReceive('addMessage')
            ->once()
            ->with($ticketId, Mockery::on(fn($m) => $m['message'] === 'Reply message'))
            ->andReturn($message);

        $result = $this->service->replyTicket($ticketId, $data);

        $this->assertInstanceOf(SupportedTicketMessage::class, $result);
        Event::assertDispatched(TicketReplied::class);
    }

    /** @test */
    public function it_changes_ticket_status()
    {
        $ticketId = 1;
        $status = 'closed';

        $ticket = new SupportedTicket(['id' => $ticketId, 'status' => $status]);

        $this->repoMock
            ->shouldReceive('updateTicket')
            ->once()
            ->with($ticketId, ['status' => $status])
            ->andReturn($ticket);

        $result = $this->service->changeStatus($ticketId, $status);

        $this->assertInstanceOf(SupportedTicket::class, $result);
        $this->assertEquals('closed', $result->status);
    }

    /** @test */
    public function it_gets_user_tickets()
    {
        $userId = 1;
        $tickets = collect([new SupportedTicket(['id' => 1, 'customer_id' => $userId])]);

        $this->repoMock
            ->shouldReceive('listUserTickets')
            ->once()
            ->with($userId)
            ->andReturn($tickets);

        $result = $this->service->getUserTickets($userId);

        $this->assertCount(1, $result);
        $this->assertEquals($userId, $result[0]->customer_id);
    }

    /** @test */
    public function it_gets_ticket_by_id()
    {
        $ticket = new SupportedTicket(['id' => 1, 'title' => 'Test Ticket']);

        $this->repoMock
            ->shouldReceive('findTicketById')
            ->once()
            ->with(1)
            ->andReturn($ticket);

        $result = $this->service->getTicket(1);

        $this->assertInstanceOf(SupportedTicket::class, $result);
        $this->assertEquals('Test Ticket', $result->title);
    }
}