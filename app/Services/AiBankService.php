<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AiBankService
{

    protected string $apiKey;
    protected string $baseUrl;
    protected string $model;
    protected int $timeout;

    public function __construct()
    {
        $this->apiKey  = (string) config('gemini.api_key');
        $this->baseUrl = rtrim(config('gemini.base_url'), '/');
        $this->model   = config('gemini.model', 'gemini-2.0-flash');
        $this->timeout = config('gemini.timeout', 30);

        if (empty($this->apiKey)) {
            throw new \RuntimeException('Gemini API key missing');
        }
    }

    protected function systemPrompt(): string
    {
        return implode("\n", [
            "أنت مستشار مالي ذكي.",
            "مهمتك تقديم توصيات مالية شخصية بناءً على سؤال المستخدم وعاداته المالية العامة.",
            "لا تقدم نصائح استثمارية عالية المخاطر.",
            "قدم اقتراحات عملية وواضحة يمكن تنفيذها فورًا.",
            "استخدم لغة بسيطة وواضحة بدون تعقيد.",
        ]);
    }

    protected function postChat(array $messages): string
    {
        // Gemini accepts only ONE prompt (text),
        // so we concatenate messages into a single prompt.
        $contents = [];
        foreach ($messages as $msg) {
            $contents[] = strtoupper($msg['role']) . ": " . $msg['content'];
        }

        $prompt = implode("\n\n", $contents);

        $payload = [
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ]
        ];

        $url = $this->baseUrl . "/models/{$this->model}:generateContent?key={$this->apiKey}";

        $response = Http::acceptJson()
            ->asJson()
            ->timeout($this->timeout)
            ->post($url, $payload);

        if (!$response->successful()) {
            throw new \RuntimeException(
                "Gemini request failed: {$response->status()} - {$response->body()}"
            );
        }

        $data = $response->json();
        return $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }

    public function recommendation(string $query, string $lang = 'ar'): string
    {
        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt()],
            [
                'role' => 'user',
                'content' => "اللغة: {$lang}\nالمهمة: قدم توصيات مالية عملية.\nسؤال المستخدم: {$query}",
            ],
        ];

        return $this->postChat($messages);
    }
}
