<?php

namespace Tests\Feature;

use App\Services\ReportsService;
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

        // Mock ReportsService
        $this->reportsService = Mockery::mock(ReportsService::class);
        $this->app->instance(ReportsService::class, $this->reportsService);
    }

    #[Test]
    public function test_index_returns_daily_reports_by_default()
    {
        $dto = (object)['data' => ['daily_report' => 'ok']];

        $this->reportsService->shouldReceive('getByRange')
            ->with('daily')
            ->once()
            ->andReturn($dto);

        $response = $this->getJson('/api/reports');

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'range'  => 'daily',
                     'data'   => ['daily_report' => 'ok'],
                 ]);
    }

    #[Test]
    public function test_index_returns_weekly_reports()
    {
        $dto = (object)['data' => ['weekly_report' => 'ok']];

        $this->reportsService->shouldReceive('getByRange')
            ->with('weekly')
            ->once()
            ->andReturn($dto);

        $response = $this->getJson('/api/reports?range=weekly');

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'range'  => 'weekly',
                     'data'   => ['weekly_report' => 'ok'],
                 ]);
    }

    #[Test]
    public function test_index_returns_monthly_reports()
    {
        $dto = (object)['data' => ['monthly_report' => 'ok']];

        $this->reportsService->shouldReceive('getByRange')
            ->with('monthly')
            ->once()
            ->andReturn($dto);

        $response = $this->getJson('/api/reports?range=monthly');

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'range'  => 'monthly',
                     'data'   => ['monthly_report' => 'ok'],
                 ]);
    }

    #[Test]
    public function test_index_with_invalid_range_still_returns_data()
    {
        $dto = (object)['data' => ['custom_report' => 'ok']];

        $this->reportsService->shouldReceive('getByRange')
            ->with('yearly')
            ->once()
            ->andReturn($dto);

        $response = $this->getJson('/api/reports?range=yearly');

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'range'  => 'yearly',
                     'data'   => ['custom_report' => 'ok'],
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

        $response = $this->getJson('/api/reports/account-summaries');

        $response->assertStatus(200)
                 ->assertJson($data);
    }
}