<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\RecommendationService;
use App\Repositories\TransactionRepositoryInterface;
use Illuminate\Support\Collection;

class RecommendationServiceTest extends TestCase
{
    protected $repo;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repo = $this->createMock(TransactionRepositoryInterface::class);
        $this->service = new RecommendationService($this->repo);
    }

    /** ✅ اختبار buildAccountSummary */
    public function test_build_account_summary()
    {
        $accountId = 1;

        // تجهيز بيانات وهمية
        $last = collect([
            ['when' => '2025-12-01', 'merchant' => 'Amazon', 'category' => 'Shopping', 'amount' => 120, 'currency' => 'USD'],
            ['when' => '2025-12-02', 'merchant' => 'Netflix', 'category' => 'Entertainment', 'amount' => 15, 'currency' => 'USD'],
        ]);
        $monthly = [200, 250, 300];
        $categories = [
            ['category' => 'Shopping', 'total' => 500],
            ['category' => 'Food', 'total' => 300],
        ];
        $recurrings = [
            ['merchant' => 'Netflix', 'times' => 6],
        ];
        $large = collect([
            ['merchant' => 'Apple Store', 'amount' => 999],
        ]);

        // إعداد الـ Mock
        $this->repo->method('lastNByAccount')->willReturn($last);
        $this->repo->method('monthlySpendingSummaryByAccount')->willReturn($monthly);
        $this->repo->method('categorySpendingByAccount')->willReturn($categories);
        $this->repo->method('recurringMerchantsByAccount')->willReturn($recurrings);
        $this->repo->method('largeTransactionsByAccount')->willReturn($large);

        $summary = $this->service->buildAccountSummary($accountId);

        $this->assertEquals($last->toArray(), $summary['last_transactions']);
        $this->assertEquals($monthly, $summary['monthly_summary']);
        $this->assertEquals(round(array_sum($monthly) / count($monthly), 2), $summary['avg_monthly_spend']);
        $this->assertEquals($categories, $summary['top_categories']);
        $this->assertEquals($recurrings, $summary['recurring']);
        $this->assertEquals($large->toArray(), $summary['large']);
    }

    /** ✅ اختبار buildUserPromptFromSummary */
    public function test_build_user_prompt_from_summary()
    {
        $summary = [
            'last_transactions' => [
                ['when' => '2025-12-01', 'merchant' => 'Amazon', 'category' => 'Shopping', 'amount' => 120, 'currency' => 'USD'],
                ['when' => '2025-12-02', 'merchant' => 'Netflix', 'category' => 'Entertainment', 'amount' => 15, 'currency' => 'USD'],
            ],
            'monthly_summary' => [200, 250, 300],
            'avg_monthly_spend' => 250,
            'top_categories' => [
                ['category' => 'Shopping', 'total' => 500],
                ['category' => 'Food', 'total' => 300],
            ],
            'recurring' => [
                ['merchant' => 'Netflix', 'times' => 6],
            ],
            'large' => [
                ['merchant' => 'Apple Store', 'amount' => 999],
            ],
        ];

        $prompt = $this->service->buildUserPromptFromSummary($summary);

        $this->assertStringContainsString('متوسط الإنفاق الشهري: 250 USD', $prompt);
        $this->assertStringContainsString('Shopping (500)', $prompt);
        $this->assertStringContainsString('Netflix (6)', $prompt);
        $this->assertStringContainsString('Apple Store: 999', $prompt);
        $this->assertStringContainsString('Amazon', $prompt);
        $this->assertStringContainsString('Netflix', $prompt);
    }
}