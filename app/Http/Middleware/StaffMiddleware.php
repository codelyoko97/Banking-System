<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class StaffMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // إذا ما في مستخدم أو الدور مش 5 → ممنوع
        if (! $user || $user->role_id !== 5) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // إذا الدور 5 → كمل الطلب
        return $next($request);
    }
}