<?php

namespace App\Providers;

use App\Repositories\EloquentTicketRepository;
use App\Repositories\TicketRepositoryInterface;
use App\Repositories\TransactionRepository;
use App\Repositories\TransactionRepositoryInterface;
use App\Services\DatabaseNotificationAdapter;
use App\Services\NotificationAdapterInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

  /**
   * Register any application services.
   */
  public function register(): void
  {
    $this->app->bind(\App\Repositories\UserRepositoryInterface::class, \App\Repositories\EloquentUserRepository::class);

    $this->app->bind(\App\Repositories\AccountRepositoryInterface::class, \App\Repositories\EloquentAccountRepository::class);

    $this->app->bind(TransactionRepositoryInterface::class, TransactionRepository::class);

     $this->app->bind(TicketRepositoryInterface::class, EloquentTicketRepository::class);
     $this->app->bind(NotificationAdapterInterface::class, DatabaseNotificationAdapter::class);


  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    //
  }

}
