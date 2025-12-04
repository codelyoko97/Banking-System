<?php

namespace App\Console\Commands;

use App\Services\ScheduledTransactionService;
use Illuminate\Console\Command;

class RunScheduledTransactions extends Command
{
  protected $signature = 'scheduled:run';
  protected $description = 'Run due scheduled transactions';

  public function handle(ScheduledTransactionService $service): int
  {
    $count = $service->runDue();
    $this->info("Processed {$count} scheduled transactions.");
    return self::SUCCESS;
  }
}
