<?php

namespace App\Events;

use App\Models\SupportedTicketMessage;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketReplied
{
    use Dispatchable, SerializesModels;

    public SupportedTicketMessage $message;

    public function __construct(SupportedTicketMessage $message)
    {
        $this->message = $message;
    }
}
