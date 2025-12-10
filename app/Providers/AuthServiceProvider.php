<?php

namespace App\Providers;

use App\Models\SupportedTicket;
use App\Policies\TicketPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
  /**
   * The policy mappings for the application.
   *
   * @var array<class-string, class-string>
   */
  protected $policies = [
    SupportedTicket::class => TicketPolicy::class,
  ];

  /**
   * Register any authentication / authorization services.
   */
  public function boot(): void
  {
    $this->registerPolicies();

    Gate::define('make-transaction', function ($user) {
      return $user->role_id == 4;
    });
    // Gate: approve transaction => Teller or Manager
    Gate::define('approve-transaction', fn($user) => $user->hasRole(['Teller', 'Manager']));

    // Gate: access admin dashboard => Admin or Manager
    Gate::define('access-admin-dashboard', fn($user) => $user->hasRole(['Admin', 'Manager', 'Support Agent']));

    // Gate: view logs => Admin or Auditor
    Gate::define('view-logs', fn($user) => $user->hasRole(['Admin', 'Auditor']));

    // Gate: create account => Customer (مثال إنو العملاء فقط بيفتحو حساب لأنك طلبت كذا قبل)
    Gate::define('create-account', fn($user) => $user->hasRole('Customer'));

    // Gate: reply ticket as staff => Support Agent or Admin
    Gate::define('reply-ticket-as-staff', fn($user) => $user->hasRole(['Support Agent', 'Admin']));


    Gate::define(
      'change-ticket-status',
      fn($user) =>
      $user->hasRole(['Support Agent', 'Manager', 'Admin'])
    );


    Gate::define('manage-staff', fn($user) => $user->hasRole(['Admin', 'Manager']));
    Gate::define('view-staff', fn($user) => $user->hasRole(['Admin', 'Manager', 'Auditor']));

    Gate::define(
      'download-reports',
      fn($u) =>
      $u->hasRole(['Admin', 'Manager', 'Auditor'])
    );
  }
}
