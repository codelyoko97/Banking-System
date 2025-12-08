<?php

namespace App\Providers;

use App\Repositories\DashboardRepository;
use App\Repositories\DashboardRepositoryInterface;
use App\Repositories\EloquentReportsRepository;
use App\Repositories\EloquentTicketRepository;
use App\Repositories\ReportsRepositoryInterface;
use App\Repositories\TicketRepositoryInterface;
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
    $this->app->bind(\App\Repositories\TransactionRepositoryInterface::class, \App\Repositories\EloquentTransactionRepository::class);
    $this->app->bind(TicketRepositoryInterface::class, EloquentTicketRepository::class);
    $this->app->bind(NotificationAdapterInterface::class, DatabaseNotificationAdapter::class);
    $this->app->bind(DashboardRepositoryInterface::class, DashboardRepository::class);
    $this->app->bind(ReportsRepositoryInterface::class, EloquentReportsRepository::class);
  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    //
  }
}
