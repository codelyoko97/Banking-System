<?php

namespace App\Providers;

use App\Repositories\TransactionRepository;
use App\Repositories\TransactionRepositoryInterface;
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
  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    //
  }
}
