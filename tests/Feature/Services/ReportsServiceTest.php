<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\ReportsService;
use App\Repositories\ReportsRepositoryInterface;
use App\DTO\TransactionReportDTO;
use App\DTO\AccountSummaryDTO;
use Illuminate\Support\Facades\Cache;

class ReportsServiceTest extends TestCase
{
    protected $repo;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush(); // تنظيف الكاش قبل كل اختبار
        $this->repo = $this->createMock(ReportsRepositoryInterface::class);
        $this->service = new ReportsService($this->repo);
    }

    /** ✅ daily */
    public function test_daily_report()
    {
        $rows = [[
            'id' => 1,
            'account_id' => 10,
            'status' => 'completed',
            'amount' => 100,
            'type' => 'deposit',
            'employee_name' => 'Ali',
            'created_at' => '2025-12-11',
            'description' => 'Test transaction',
        ]];
        $this->repo->method('getTransactionsDaily')->willReturn($rows);

        $result = $this->service->daily();

        $expected = [[
            'id' => 1,
            'account_id' => 10,
            'status' => 'completed',
            'amount' => 100.0, // float
            'type' => 'deposit',
            'employee' => 'Ali',
            'date' => '2025-12-11',
            'description' => 'Test transaction',
        ]];

        $this->assertInstanceOf(TransactionReportDTO::class, $result);
        $this->assertEquals($expected, $result->data);
    }

    /** ✅ weekly */
    public function test_weekly_report()
    {
        $rows = [[
            'id' => 2,
            'account_id' => 20,
            'status' => 'pending',
            'amount' => 200,
            'type' => 'withdraw',
            'employee_name' => 'Sara',
            'created_at' => '2025-12-12',
            'description' => 'Weekly transaction',
        ]];
        $this->repo->method('getTransactionsWeekly')->willReturn($rows);

        $result = $this->service->weekly();

        $expected = [[
            'id' => 2,
            'account_id' => 20,
            'status' => 'pending',
            'amount' => 200.0,
            'type' => 'withdraw',
            'employee' => 'Sara',
            'date' => '2025-12-12',
            'description' => 'Weekly transaction',
        ]];

        $this->assertInstanceOf(TransactionReportDTO::class, $result);
        $this->assertEquals($expected, $result->data);
    }

    /** ✅ monthly */
    public function test_monthly_report()
    {
        $rows = [[
            'id' => 3,
            'account_id' => 30,
            'status' => 'failed',
            'amount' => 300,
            'type' => 'transfer',
            'employee_name' => 'Omar',
            'created_at' => '2025-12-13',
            'description' => 'Monthly transaction',
        ]];
        $this->repo->method('getTransactionsMonthly')->willReturn($rows);

        $result = $this->service->monthly();

        $expected = [[
            'id' => 3,
            'account_id' => 30,
            'status' => 'failed',
            'amount' => 300.0,
            'type' => 'transfer',
            'employee' => 'Omar',
            'date' => '2025-12-13',
            'description' => 'Monthly transaction',
        ]];

        $this->assertInstanceOf(TransactionReportDTO::class, $result);
        $this->assertEquals($expected, $result->data);
    }

    /** ✅ accountSummaries */
    public function test_account_summaries()
    {
        $rows = [[
            'account_id' => 1,
            'balance' => 500,
            'transactions' => 20,
        ]];
        $this->repo->method('getAccountSummaries')->willReturn($rows);

        $result = $this->service->accountSummaries();

        $this->assertInstanceOf(AccountSummaryDTO::class, $result);
        $this->assertEquals($rows, $result->data);
    }

    /** ✅ getByRange */
    public function test_get_by_range()
    {
        $dailyRows = [[
            'id' => 1,
            'account_id' => 10,
            'status' => 'completed',
            'amount' => 100,
            'type' => 'deposit',
            'employee_name' => 'Ali',
            'created_at' => '2025-12-11',
            'description' => 'Daily transaction',
        ]];
        $weeklyRows = [[
            'id' => 2,
            'account_id' => 20,
            'status' => 'pending',
            'amount' => 200,
            'type' => 'withdraw',
            'employee_name' => 'Sara',
            'created_at' => '2025-12-12',
            'description' => 'Weekly transaction',
        ]];
        $monthlyRows = [[
            'id' => 3,
            'account_id' => 30,
            'status' => 'failed',
            'amount' => 300,
            'type' => 'transfer',
            'employee_name' => 'Omar',
            'created_at' => '2025-12-13',
            'description' => 'Monthly transaction',
        ]];

        $this->repo->method('getTransactionsDaily')->willReturn($dailyRows);
        $this->repo->method('getTransactionsWeekly')->willReturn($weeklyRows);
        $this->repo->method('getTransactionsMonthly')->willReturn($monthlyRows);

        $daily = $this->service->getByRange('daily');
        $weekly = $this->service->getByRange('weekly');
        $monthly = $this->service->getByRange('monthly');
        $default = $this->service->getByRange('something_else');

        $this->assertEquals(100.0, $daily->data[0]['amount']);
        $this->assertEquals(200.0, $weekly->data[0]['amount']);
        $this->assertEquals(300.0, $monthly->data[0]['amount']);
        $this->assertEquals(100.0, $default->data[0]['amount']); // default يرجع daily
    }
}