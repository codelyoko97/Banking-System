<?php

namespace Tests\Unit;

use App\Banking\Transactions\Strategies\TransferStrategy;
use App\DTO\ProcessTransactionDTO;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class TransferStrategyTest extends TestCase
{
    use RefreshDatabase;

    public function test_transfer_creates_transaction_and_updates_balances()
    {
        // حساب المصدر برصيد 500
        $src = Account::factory()->create([
            'balance' => 500.0000,
            'number'  => 'AC_SRC'
        ]);

        // حساب الوجهة برصيد 200
        $dst = Account::factory()->create([
            'balance' => 200.0000,
            'number'  => 'AC_DST'
        ]);

        // تأكد إن الحسابات ما عندها ميزات
        DB::table('account_features')->where('account_id', $src->id)->delete();
        DB::table('account_features')->where('account_id', $dst->id)->delete();

        // DTO للتحويل 100 من المصدر للوجهة
        $dto = new ProcessTransactionDTO(
            account_id: 'AC_SRC',
            amount: 100.00,
            type: 'transfer',
            account_related_id: 'AC_DST',
            description: null,
            employee_name: null,
            requestedBy: null
        );

        $strategy = new TransferStrategy;
        $txn = $strategy->execute($dto, null);

        // تحقق من نوع العملية والمبلغ
        $this->assertEquals('transfer', $txn->type);
        $this->assertEquals(100.00, (float)$txn->amount);

        // تحقق من تحديث الأرصدة
        $this->assertEquals("400.0000", $src->fresh()->balance);
        $this->assertEquals("300.0000", $dst->fresh()->balance);
    }
}