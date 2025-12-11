<?php

namespace Tests\Unit\Events;

use App\Events\TicketCreated;
use App\Models\SupportedTicket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketCreatedTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_stores_ticket_instance()
    {
        $ticket = SupportedTicket::factory()->create([
            'title' => 'Support Needed',
            'message' => 'Initial message',
        ]);

        $event = new TicketCreated($ticket);

        $this->assertEquals($ticket->id, $event->ticket->id);
        $this->assertEquals('Support Needed', $event->ticket->title);
        $this->assertEquals('Initial message', $event->ticket->message);
    }

    public function test_event_handles_ticket_with_null_title()
    {
        $ticket = SupportedTicket::factory()->make([
            'title' => null,
            'message' => 'Fallback message',
        ]);

        $event = new TicketCreated($ticket);

        $this->assertNull($event->ticket->title);
        $this->assertEquals('Fallback message', $event->ticket->message);
    }
}