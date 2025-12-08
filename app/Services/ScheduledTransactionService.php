<?php

namespace App\Services;

use App\Banking\Transactions\Approval\ManagerApproval;
use App\Banking\Transactions\Approval\SystemApproval;
use App\Banking\Transactions\Approval\TellerApproval;
use App\DTO\ProcessTransactionDTO;
use App\Models\SchedualeTransaction;
use App\Models\Transaction;
use Carbon\Carbon;

class ScheduledTransactionService
{
  public function runDue(): int
  {
    $now = Carbon::now();

    $plans = SchedualeTransaction::query()
      ->where('active', true)
      ->where('next_run', '<=', $now)
      ->get();

    $count = 0;
    foreach ($plans as $plan) {
      $this->processPlan($plan);
      $this->updateNextRun($plan);
      $count++;
    }

    return $count;
  }

  protected function processPlan(SchedualeTransaction $plan): Transaction
  {
    $system  = new SystemApproval();
    $teller  = new TellerApproval();
    $manager = new ManagerApproval();
    $system->setNext($teller, 4)->setNext($manager, 2);

    $dto = new ProcessTransactionDTO(
      $plan->account->number,
      $plan->amount,
      $plan->type,
      $plan->account_related_id ? $plan->relatedAccount->number : null,
      'Scheduled transaction',
      'Scheduler',
      null
    );

    return $system->approve($dto);
  }

  protected function updateNextRun(SchedualeTransaction $plan): void
  {
    $next = match ($plan->frequency) {
      'daily'   => Carbon::parse($plan->next_run)->addDay(),
      'weekly'  => Carbon::parse($plan->next_run)->addWeek(),
      'monthly' => $plan->day_of_month
        ? Carbon::parse($plan->next_run)->addMonth()->day((int)$plan->day_of_month)
        : Carbon::parse($plan->next_run)->addMonth(),
      default   => Carbon::parse($plan->next_run)->addDay(),
    };

    if ($plan->end_date && $next->gt(Carbon::parse($plan->end_date))) {
      $plan->update(['active' => false]);
      return;
    }

    $plan->update(['next_run' => $next]);
  }
}
