<?php

namespace Tests\Helpers;

use App\Models\Log;

trait DisableLogs
{
    protected function disableLogs(): void
    {
        // نلغي أي عمليات حفظ/إنشاء على موديل Log أثناء الاختبار
        Log::unguard();

        Log::saving(function ($model) {
            return false;
        });

        Log::creating(function ($model) {
            return false;
        });
    }
}