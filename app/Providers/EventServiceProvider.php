<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

use App\Events\TicketCreated;
use App\Events\TicketReplied;

use App\Listeners\SendNotificationOnTicketCreated;
use App\Listeners\SendNotificationOnTicketReplied;

class EventServiceProvider extends ServiceProvider
{
  public static $shouldDiscoverEvents = true;


  // protected $listen = [
  //   TicketCreated::class => [
  //     SendNotificationOnTicketCreated::class,
  //   ],
  //   TicketReplied::class => [
  //     SendNotificationOnTicketReplied::class,
  //   ],
  // ];

  public function boot(): void
  {
    //
  }
}
