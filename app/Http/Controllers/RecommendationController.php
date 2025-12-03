<?php

namespace App\Http\Controllers;

use App\Services\RecommendationService;
use App\Services\AiBankService;

class RecommendationController extends Controller
{
    public function __construct(
        protected RecommendationService $recommendation,
        protected AiBankService $ai
    ) {}

    public function recommend(int $accountId)
    {
        // 1) نبني ملخص الحساب
        $summary = $this->recommendation->buildAccountSummary($accountId);

        // 2) نبني الـ prompt من الملخص
        $prompt = $this->recommendation->buildUserPromptFromSummary($summary);

        // 3) نرسل الـ prompt إلى Gemini أو أي LLM عبر AiBankService
        $answer = $this->ai->recommendation($prompt, 'ar');

        // 4) نرجع النتيجة كـ JSON
        return response()->json([
            'summary' => $summary,
            'answer' => $answer,
        ]);
    }
}