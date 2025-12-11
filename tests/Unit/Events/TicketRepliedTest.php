<?php

namespace Tests\Unit\Events;

use App\Events\TicketReplied;
use App\Models\SupportedTicket;
use App\Models\SupportedTicketMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketRepliedTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_stores_message_instance()
    {
        // أنشئ تذكرة أولاً
        $ticket = SupportedTicket::factory()->create([
            'title' => 'Support Needed',
            'message' => 'Initial message',
        ]);

        // أنشئ رسالة مرتبطة بالتذكرة
        $message = SupportedTicketMessage::factory()->create([
            'supported_ticket_id' => $ticket->id,
            'message' => 'Reply to ticket',
        ]);

        $event = new TicketReplied($message);

        $this->assertEquals($message->id, $event->message->id);
        $this->assertEquals('Reply to ticket', $event->message->message);
        $this->assertEquals($ticket->id, $event->message->supported_ticket_id);
    }

    public function test_event_handles_message_with_empty_content()
    {
        $ticket = SupportedTicket::factory()->create([
            'title' => 'Support Needed',
            'message' => 'Initial message',
        ]);

        $message = SupportedTicketMessage::factory()->make([
            'supported_ticket_id' => $ticket->id,
            'message' => '',
        ]);

        $event = new TicketReplied($message);

        $this->assertEquals('', $event->message->message);
        $this->assertEquals($ticket->id, $event->message->supported_ticket_id);
    }
}