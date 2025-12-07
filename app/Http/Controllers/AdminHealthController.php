<?php

namespace App\Http\Controllers;

use App\Services\SystemHealthService;

class AdminHealthController extends Controller
{
    public function __construct(private SystemHealthService $svc) {}

    public function health()
    {
        return response()->json($this->svc->get());
    }
}
