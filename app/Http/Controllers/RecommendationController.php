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
        $summary = $this->recommendation->buildAccountSummary($accountId);

        $prompt = $this->recommendation->buildUserPromptFromSummary($summary);

        $answer = $this->ai->recommendation($prompt, 'ar');

        return response()->json([
            'summary' => $summary,
            'answer' => $answer,
        ]);
    }
}