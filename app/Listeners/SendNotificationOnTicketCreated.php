<?php

namespace App\Listeners;

use App\Events\TicketCreated;
use App\Services\NotificationAdapterInterface;

class SendNotificationOnTicketCreated
{
  public function handle(TicketCreated $event)
  {
    app(NotificationAdapterInterface::class)->sendToStaff("New ticket #{$event->ticket->id}: {$event->ticket->title}");
  }
}
