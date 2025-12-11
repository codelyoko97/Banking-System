<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\AdminDashboardService;
use App\Repositories\DashboardRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminDashboardServiceTest extends TestCase
{
  protected $repo;
  protected $service;

  protected function setUp(): void
  {
    parent::setUp();

    Cache::flush(); // تنظيف الكاش قبل كل اختبار
    $this->repo = $this->createMock(DashboardRepositoryInterface::class);
    $this->service = new AdminDashboardService($this->repo);
  }

  /** ✅ Weekly Transactions */
  public function test_transactions_weekly()
  {
    $rows = [['day' => 'Mon', 'total' => 5]];
    $this->repo->method('getWeeklyTransactions')->willReturn($rows);

    $result = $this->service->transactionsWeekly();

    $this->assertEquals(5, $result[0]['total']);
  }

  /** ✅ Transaction Status Counts */
  public function test_transaction_status_counts()
  {
    $rows = [['status' => 'completed', 'count' => 10]];
    $this->repo->method('getTransactionStatusCounts')->willReturn($rows);

    $result = $this->service->transactionStatusCounts();

    $this->assertEquals('completed', $result[0]['status']);
    $this->assertEquals(10, $result[0]['count']);
  }

  /** ✅ Accounts Monthly */
  public function test_accounts_monthly()
  {
    $rows = [['date' => '2025-12-01', 'count' => 3]];
    $this->repo->method('getAccountsMonthly')->willReturn($rows);

    $result = $this->service->accountsMonthly();

    $this->assertEquals(3, $result[0]['count']);
  }

  /** ✅ Top Customers */
  public function test_top_customers()
  {
    $rows = [[
      'user_id'      => 1,
      'name'         => 'Feras',
      'email'        => 'feras@test.com',
      'total_amount' => 1000.50,
      'tx_count'     => 20,
    ]];

    $this->repo->method('getTopCustomers')->willReturn($rows);

    $result = $this->service->topCustomers();

    $this->assertEquals('Feras', $result[0]['name']);
    $this->assertEquals(1000.50, $result[0]['total_amount']);
    $this->assertEquals(20, $result[0]['tx_count']);
  }

  /** ✅ Accounts Today */
  public function test_accounts_today()
  {
    $this->repo->method('getAccountsToday')->willReturn(7);

    $result = $this->service->accountsToday();

    $this->assertEquals(7, $result['count']);
  }

  /** ✅ Transactions 24h */
  public function test_transactions_24h()
  {
    $row = [
      'total'   => 15,
      'success' => 10,
      'failed'  => 3,
      'pending' => 2,
    ];
    $this->repo->method('getTransactions24h')->willReturn($row);

    $result = $this->service->transactions24h();

    $this->assertEquals(15, $result['total']);
    $this->assertEquals(10, $result['success']);
    $this->assertEquals(3, $result['failed']);
    $this->assertEquals(2, $result['pending']);
  }

  /** ✅ Get All Customers */
  public function test_get_all_customers()
  {
    $rows = [['id' => 1, 'name' => 'Customer A']];
    $this->repo->method('getAllCustomers')->willReturn($rows);

    $result = $this->service->getAllCustomers();

    $this->assertEquals('Customer A', $result[0]['name']);
  }

  /** ✅ Get All Employees */
  public function test_get_all_employees()
  {
    $rows = [['id' => 1, 'name' => 'Employee A']];
    $this->repo->method('getAllEmployees')->willReturn($rows);

    $result = $this->service->getAllEmployees();

    $this->assertEquals('Employee A', $result[0]['name']);
  }

  /** ✅ Logs مع Pagination */
  public function test_logs_with_pagination()
  {
    // أنشئ عنصر log كـ Object بدل Array
    $log = (object)[
      'id' => 1,
      'user' => (object)[
        'name' => 'Feras',
        'email' => 'feras@test.com',
      ],
      'action' => 'login',
      'description' => 'User logged in',
      'created_at' => now(),
    ];

    // ضع العنصر داخل Paginator
    $paginator = new \Illuminate\Pagination\LengthAwarePaginator([$log], 1, 20, 1);

    $this->repo->method('getLogs')->willReturn($paginator);

    $result = $this->service->logs([], 20);

    // تحقق من البنية
    $this->assertArrayHasKey('data', $result);
    $this->assertArrayHasKey('pagination', $result);

    // تحقق من القيم
    $this->assertEquals(1, $result['data'][0]['id']);
    $this->assertEquals('Feras', $result['data'][0]['user']);
    $this->assertEquals('feras@test.com', $result['data'][0]['email']);
    $this->assertEquals('login', $result['data'][0]['action']);
    $this->assertEquals('User logged in', $result['data'][0]['description']);
  }

  /** ✅ Export Logs */
  public function test_export_logs()
  {
    $rows = [[
      'id' => 1,
      'user' => ['name' => 'Feras', 'email' => 'feras@test.com'],
      'action' => 'create',
      'description' => 'Created account',
      'created_at' => '2025-12-11'
    ]];

    $this->repo->method('exportLogs')->willReturn($rows);

    $csv = $this->service->exportLogs([]);

    $this->assertStringContainsString('Feras', $csv);
    $this->assertStringContainsString('Created account', $csv);
  }
}
