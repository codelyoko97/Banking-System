<?php

namespace Tests\Feature\Console;

use App\Console\Commands\RunScheduledTransactions;
use App\Services\ScheduledTransactionService;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class RunScheduledTransactionsTest extends TestCase
{
  /** ✅ الحالة الطبيعية: الخدمة ترجع عدد > 0 */
  public function test_command_runs_and_outputs_count()
  {
    $this->mock(ScheduledTransactionService::class, function ($mock) {
      $mock->shouldReceive('runDue')->once()->andReturn(5);
    });

    $this->artisan('scheduled:run')
      ->expectsOutput('Processed 5 scheduled transactions.')
      ->assertExitCode(RunScheduledTransactions::SUCCESS);
  }

  /** ✅ الحالة بدون عمليات: الخدمة ترجع 0 */
  public function test_command_runs_with_no_transactions()
  {
    $this->mock(ScheduledTransactionService::class, function ($mock) {
      $mock->shouldReceive('runDue')->once()->andReturn(0);
    });

    $this->artisan('scheduled:run')
      ->expectsOutput('Processed 0 scheduled transactions.')
      ->assertExitCode(RunScheduledTransactions::SUCCESS);
  }

  /** ❌ الحالة الاستثنائية: الخدمة ترمي Exception */
  public function test_command_handles_exception()
  {
    $this->mock(ScheduledTransactionService::class, function ($mock) {
      $mock->shouldReceive('runDue')->once()->andThrow(new \RuntimeException('Service failed'));
    });

    $this->artisan('scheduled:run')
      ->expectsOutputToContain('Service failed') // Laravel يطبع رسالة الخطأ
      ->assertExitCode(RunScheduledTransactions::FAILURE);
  }
}
