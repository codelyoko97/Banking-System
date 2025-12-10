<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\AccountRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AccountFeaturesController extends Controller
{
    public function __construct(private AccountRepositoryInterface $repo) {}

    // الحصول على الميزات
    public function index($accountId)
    {
        return response()->json([
            'account_id' => $accountId,
            'features' => $this->repo->getFeatures($accountId)
        ]);
    }

    // إضافة ميزة
    public function store(Request $request, $accountId)
    {
        $request->validate([
            'feature' => [
                'required',
                Rule::in(['overdraft', 'insurance', 'premium'])
            ]
        ]);

        // منع التكرار
        $existing = $this->repo->getFeatures($accountId);
        if (in_array($request->feature, $existing)) {
            return response()->json([
                'message' => "Feature already exists"
            ], 409);
        }

        DB::table('account_features')->insert([
            'account_id' => $accountId,
            'feature' => $request->feature,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Feature added']);
    }

    // حذف ميزة
    public function destroy($accountId, $feature)
    {
        DB::table('account_features')
            ->where('account_id', $accountId)
            ->where('feature', $feature)
            ->delete();

        return response()->json(['message' => 'Feature removed']);
    }
}
