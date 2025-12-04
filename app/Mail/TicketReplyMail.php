<?php

namespace App\Mail;

use App\Models\SupportedTicket;
use App\Models\SupportedTicketMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public SupportedTicket $ticket;
    public SupportedTicketMessage $message;

    public function __construct(SupportedTicket $ticket, SupportedTicketMessage $message)
    {
        $this->ticket = $ticket;
        $this->message = $message;
    }

    public function build()
    {
        return $this->subject("رد جديد على تذكرتك #{$this->ticket->id}")
            ->view('emails.ticket_reply');
    }
}
