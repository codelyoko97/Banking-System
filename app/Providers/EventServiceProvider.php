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
  //   \App\Events\TransactionCreated::class => [
  //     \App\Listeners\SendNotificationOnTransactionCreated::class,
  //   ],
  //   \App\Events\TransactionApproved::class => [
  //     \App\Listeners\SendNotificationOnTransactionApproved::class,
  //   ],
  //   \App\Events\TransactionRejected::class => [
  //     \App\Listeners\SendNotificationOnTransactionRejected::class,
  //   ],
  // ];

  public function boot(): void
  {
    //
  }
}
