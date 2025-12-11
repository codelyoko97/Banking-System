<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use App\Models\SchedualeTransaction;
use App\Services\ScheduledTransactionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduledTransactionServiceTest extends TestCase
{
  use RefreshDatabase;

  /** ✅ اختبار تنفيذ خطة يومية */
  public function test_run_due_executes_daily_plan()
  {
    $user = User::factory()->create();
    $account = Account::factory()->create(['customer_id' => $user->id]);

    $plan = SchedualeTransaction::factory()->create([
      'account_id' => $account->id,
      'amount' => 100,
      'type' => 'deposit',
      'frequency' => 'daily',
      'next_run' => Carbon::now()->subDay(),
      'active' => true,
    ]);

    $service = new ScheduledTransactionService();
    $count = $service->runDue();

    $this->assertEquals(1, $count);
    $plan->refresh();
    // $this->assertTrue($plan->next_run->gt(Carbon::now())); // تم تحديث next_run
  }

  /** ✅ اختبار إيقاف خطة بعد انتهاء التاريخ */
  public function test_plan_becomes_inactive_after_end_date()
  {
    $user = User::factory()->create();
    $account = Account::factory()->create(['customer_id' => $user->id]);

    $plan = SchedualeTransaction::factory()->create([
      'account_id' => $account->id,
      'amount' => 200,
      'type' => 'deposit',
      'frequency' => 'daily',
      'next_run' => Carbon::now()->subDay(),
      'end_date' => Carbon::now()->subDay(), // انتهى بالفعل
      'active' => true,
    ]);

    $service = new ScheduledTransactionService();
    $service->runDue();

    $plan->refresh();
    $this->assertFalse($plan->active); // تم تعطيل الخطة
  }

  /** ✅ اختبار خطة أسبوعية */
  public function test_run_due_updates_weekly_plan()
  {
    $user = User::factory()->create();
    $account = Account::factory()->create(['customer_id' => $user->id]);

    $plan = SchedualeTransaction::factory()->create([
      'account_id' => $account->id,
      'amount' => 300,
      'type' => 'deposit',
      'frequency' => 'weekly',
      'next_run' => Carbon::now()->subWeek(),
      'active' => true,
    ]);
    $oldNextRun = Carbon::parse($plan->next_run);

    $service = new ScheduledTransactionService();
    $service->runDue();

    $plan->refresh();

    $this->assertEquals(
      $oldNextRun->addWeek()->format('Y-m-d'),
      Carbon::parse($plan->next_run)->format('Y-m-d')
    );
  }

  /** ✅ اختبار خطة شهرية مع يوم محدد */
  public function test_run_due_updates_monthly_plan_with_day_of_month()
  {
    $user = User::factory()->create();
    $account = Account::factory()->create(['customer_id' => $user->id]);

    $plan = SchedualeTransaction::factory()->create([
      'account_id' => $account->id,
      'amount' => 400,
      'type' => 'deposit',
      'frequency' => 'monthly',
      'day_of_month' => 15,
      'next_run' => Carbon::now()->subMonth(),
      'active' => true,
    ]);

    $service = new ScheduledTransactionService();
    $service->runDue();

    $plan->refresh();
    $this->assertEquals(15, Carbon::parse($plan->next_run)->day);
  }
}
