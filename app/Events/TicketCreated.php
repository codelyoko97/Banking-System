<?php

namespace App\Events;

use App\Models\SupportedTicket;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketCreated
{
    use Dispatchable, SerializesModels;

    public SupportedTicket $ticket;

    public function __construct(SupportedTicket $ticket)
    {
        $this->ticket = $ticket;
    }
}
