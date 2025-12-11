<?php

namespace Tests\Integration;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Repositories\EloquentAccountRepository;
use Illuminate\Support\Facades\DB;

class AccountRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_features()
    {
        DB::table('account_features')->insert([
            'account_id' => 1,
            'feature' => 'premium'
        ]);

        $repo = new EloquentAccountRepository();

        $features = $repo->getFeatures(1);

        $this->assertContains('premium', $features);
    }
}
