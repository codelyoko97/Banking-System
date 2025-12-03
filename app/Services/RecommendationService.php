<?php

namespace App\Services;

use App\Repositories\TransactionRepositoryInterface;

class RecommendationService
{
    public function __construct(private TransactionRepositoryInterface $txRepo) {}

    public function buildAccountSummary(int $accountId): array
    {
        $last = $this->txRepo->lastNByAccount($accountId, 50);
        $monthly = $this->txRepo->monthlySpendingSummaryByAccount($accountId, 6);
        $categories = $this->txRepo->categorySpendingByAccount($accountId, 3);
        $recurrings = $this->txRepo->recurringMerchantsByAccount($accountId, 6, 2);
        $large = $this->txRepo->largeTransactionsByAccount($accountId, 3, null);

        $avgMonthly = count($monthly) ? array_sum($monthly) / count($monthly) : 0;

        return [
            'last_transactions' => $last->toArray(),
            'monthly_summary' => $monthly,
            'avg_monthly_spend' => round($avgMonthly, 2),
            'top_categories' => $categories,
            'recurring' => $recurrings,
            'large' => $large->toArray(),
        ];
    }

    protected function txnToText(array $txn): string
    {
        $currency = $txn['currency'] ?? 'USD';
        return "{$txn['when']} — {$txn['merchant']} — {$txn['category']} — {$txn['amount']} {$currency}";
    }

    public function buildUserPromptFromSummary(array $summary): string
    {
        $topCats = implode(', ', array_map(fn($c)=> "{$c['category']} ({$c['total']})", $summary['top_categories']));
        $recurr = implode(', ', array_map(fn($r)=> "{$r['merchant']} ({$r['times']})", $summary['recurring']));
        $largeStr = implode(', ', array_map(fn($l)=> "{$l['merchant']}: {$l['amount']}", $summary['large']));

        $examples = collect($summary['last_transactions'])->take(6)->map(fn($t)=> $this->txnToText($t))->values();
        $examplesStr = $examples->map(fn($t, $i)=> ($i+1) . ") " . $t)->implode("\n");

        return "المهمة: قدم 3 توصيات عملية لحفظ المال وتحسين الميزانية الشهرية لهذا المستخدم بناءً على بياناته.\n"
            . "معلومات المستخدم (ملخّص):\n"
            . "- متوسط الإنفاق الشهري: {$summary['avg_monthly_spend']} USD\n"
            . "- أعلى فئات الإنفاق: {$topCats}\n"
            . "- معاملات متكررة (اشتراكات): {$recurr}\n"
            . "- معاملات كبيرة مؤخراً: {$largeStr}\n\n"
            . "أمثلة على معاملات (أغلب الصلة):\n{$examplesStr}\n\n"
            . "المطلوب:\n"
            . "1) اذكر التوصية بصيغة قصيرة (1 سطر).\n"
            . "2) اشرح السبب بإيجاز (1-2 جملة).\n"
            . "3) اذكر تأثير تقديري (مثل: توفير 30 USD/month).\n"
            . "4) خطوات تنفيذ بسيطة (2-3 خطوات).\n"
            . "أجب باللغة العربية الفصحى القصيرة.";
    }

    
}