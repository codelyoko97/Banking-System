<?php

namespace App\Listeners;

use App\Events\TicketReplied;
use App\Jobs\SendTicketReplyEmail;
use App\Services\NotificationAdapterInterface;

class SendNotificationOnTicketReplied
{
  public function handle(TicketReplied $event)
  {
    $msg = $event->message;
    $ticket = $event->message->ticket;
    app(NotificationAdapterInterface::class)->sendToUser(
      $ticket->customer_id,
      "New reply on your ticket #{$ticket->id}",
      $event->message->message,
      "reply tikit",
    );

    dispatch(new SendTicketReplyEmail($ticket, $msg));
  }
}
