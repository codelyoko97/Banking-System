<?php

namespace Tests\Feature;

use App\Services\ReportsService;
use App\DTO\TransactionReportDTO;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class ReportsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $reportsService;

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();

        $this->reportsService = Mockery::mock(ReportsService::class);
        $this->app->instance(ReportsService::class, $this->reportsService);
    }

    #[Test]
    public function test_index_returns_daily_reports_by_default()
    {
        $dto = new TransactionReportDTO([
            [
                'id' => 1,
                'account_id' => 55,
                'status' => 'approved',
                'amount' => 1500,
                'type' => 'withdraw',
                'employee_name' => 'John Doe',
                'created_at' => '2025-01-01 10:00:00',
                'description' => 'Daily test transaction'
            ]
        ]);

        $this->reportsService->shouldReceive('getByRange')
            ->with('daily')
            ->once()
            ->andReturn($dto);

        $response = $this->getJson('/api/admin/reports/transactions');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'range'  => 'daily',
                'data'   => [
                    [
                        'id' => 1,
                        'account_id' => 55,
                        'status' => 'approved',
                        'amount' => 1500,
                        'type' => 'withdraw',
                        'employee' => 'John Doe',
                        'date' => '2025-01-01 10:00:00',
                        'description' => 'Daily test transaction'
                    ]
                ],
            ]);
    }

    #[Test]
    public function test_index_returns_weekly_reports()
    {
        $dto = new TransactionReportDTO([
            [
                'id' => 2,
                'account_id' => 77,
                'status' => 'pending',
                'amount' => 2000,
                'type' => 'deposit',
                'employee_name' => 'Jane Doe',
                'created_at' => '2025-01-02 11:00:00',
                'description' => 'Weekly test transaction'
            ]
        ]);

        $this->reportsService->shouldReceive('getByRange')
            ->with('weekly')
            ->once()
            ->andReturn($dto);

        $response = $this->getJson('/api/admin/reports/transactions?range=weekly');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'range'  => 'weekly',
                'data'   => [
                    [
                        'id' => 2,
                        'account_id' => 77,
                        'status' => 'pending',
                        'amount' => 2000,
                        'type' => 'deposit',
                        'employee' => 'Jane Doe',
                        'date' => '2025-01-02 11:00:00',
                        'description' => 'Weekly test transaction'
                    ]
                ],
            ]);
    }

    #[Test]
    public function test_index_returns_monthly_reports()
    {
        $dto = new TransactionReportDTO([
            [
                'id' => 3,
                'account_id' => 88,
                'status' => 'rejected',
                'amount' => 500,
                'type' => 'withdraw',
                'employee_name' => 'Mark Smith',
                'created_at' => '2025-01-03 12:00:00',
                'description' => 'Monthly test transaction'
            ]
        ]);

        $this->reportsService->shouldReceive('getByRange')
            ->with('monthly')
            ->once()
            ->andReturn($dto);

        $response = $this->getJson('/api/admin/reports/transactions?range=monthly');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'range'  => 'monthly',
                'data'   => [
                    [
                        'id' => 3,
                        'account_id' => 88,
                        'status' => 'rejected',
                        'amount' => 500,
                        'type' => 'withdraw',
                        'employee' => 'Mark Smith',
                        'date' => '2025-01-03 12:00:00',
                        'description' => 'Monthly test transaction'
                    ]
                ],
            ]);
    }

    #[Test]
    public function test_index_with_invalid_range_still_returns_data()
    {
        $dto = new TransactionReportDTO([
            [
                'id' => 4,
                'account_id' => 99,
                'status' => 'approved',
                'amount' => 750,
                'type' => 'deposit',
                'employee_name' => 'Alice Brown',
                'created_at' => '2025-01-04 13:00:00',
                'description' => 'Yearly test transaction'
            ]
        ]);

        $this->reportsService->shouldReceive('getByRange')
            ->with('yearly')
            ->once()
            ->andReturn($dto);

        $response = $this->getJson('/api/admin/reports/transactions?range=yearly');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'range'  => 'yearly',
                'data'   => [
                    [
                        'id' => 4,
                        'account_id' => 99,
                        'status' => 'approved',
                        'amount' => 750,
                        'type' => 'deposit',
                        'employee' => 'Alice Brown',
                        'date' => '2025-01-04 13:00:00',
                        'description' => 'Yearly test transaction'
                    ]
                ],
            ]);
    }

    #[Test]
    public function test_account_summaries_returns_data()
    {
        $data = [
            ['account' => 'A', 'balance' => 100],
            ['account' => 'B', 'balance' => 200],
        ];

        $this->reportsService->shouldReceive('accountSummaries')
            ->once()
            ->andReturn($data);

        $response = $this->getJson('/api/admin/reports/account-summaries');

        $response->assertStatus(200)
            ->assertJson($data);
    }
}