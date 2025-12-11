<?php

namespace Tests\Unit\Banking\Transactions\States;

use App\Banking\Transactions\States\ActiveState;
use App\Models\Account;
use App\Models\Status;
use App\Models\Type;
use App\Models\User;
use App\Services\Accounts\AccountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ActiveStateTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Type $type;
    protected Status $activeStatus;

    protected function setUp(): void
    {
        parent::setUp();

        // نظافة لضمان ثبات النتائج
        DB::table('account_features')->truncate();
        DB::table('statuses')->truncate();
        DB::table('types')->truncate();
        DB::table('users')->truncate();

        // إنشاء سجلات مطلوبة للـ FK
        $this->user = User::factory()->create();

        // لا تستخدم Type::factory لأنه غير معرف — أنشئه مباشرة
        $this->type = Type::create([
            'name' => 'checking', // عدّل حسب سكيمتك إذا له أعمدة إضافية
        ]);

        // استخدم Factory أو create، حسب ما هو متاح عندك
        $this->activeStatus = Status::factory()->create([
            'name' => 'active',
        ]);
    }

    protected function makeAccount(array $overrides = []): Account
    {
        return Account::factory()->create(array_merge([
            'customer_id' => $this->user->id,
            'type_id'     => $this->type->id,
            'status_id'   => $this->activeStatus->id,
        ], $overrides));
    }

    // Helper لتثبيت سلوك AccountService داخل كل اختبار
    protected function mockDecoratedBalance(float $balance): void
    {
        $fake = new class($balance) {
            public float $balance;
            public function __construct($b) { $this->balance = $b; }
        };

        $mock = new class($fake) {
            protected $decorated;
            public function __construct($decorated) { $this->decorated = $decorated; }
            public function getDecoratedAccount($accountId) { return $this->decorated; }
        };

        // ربط الـ mock بالحاوية حتى ActiveState يستخدمه
        $this->app->instance(AccountService::class, $mock);
    }

    public function test_deposit_in_active_state_increases_balance()
    {
        $account = $this->makeAccount(['balance' => 100.00]);

        $state = new ActiveState();
        $result = $state->deposit($account, 50.00);

        $this->assertTrue($result);
        $this->assertEquals(150.00, (float)$account->fresh()->balance);
    }

    public function test_withdraw_in_active_state_decreases_balance()
    {
        $account = $this->makeAccount(['balance' => 200.00]);

        // خلي الرصيد المتاح مساويًا للرصيد الفعلي أو أكبر حتى يمر السحب
        $this->mockDecoratedBalance(200.00);

        $state = new ActiveState();
        $result = $state->withdraw($account, 50.00);

        $this->assertTrue($result);
        $this->assertEquals(150.00, (float)$account->fresh()->balance);
    }

    public function test_withdraw_in_active_state_throws_exception_if_insufficient()
    {
        $account = $this->makeAccount(['balance' => 20.00]);

        // أعطِ AccountService رصيد متاح أقل من المطلوب حتى يرمي الاستثناء
        $this->mockDecoratedBalance(20.00);

        $state = new ActiveState();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient funds');

        $state->withdraw($account, 50.00);
    }

    public function test_close_in_active_state_sets_status_to_closed()
    {
        // جهّز حالة closed
        $closedStatus = Status::create(['name' => 'closed']);

        $account = $this->makeAccount(['balance' => 0.00]);

        $state = new ActiveState();
        $result = $state->close($account);

        $this->assertTrue($result);

        // تحقق بالاسم بدل id لتفادي اختلافات المعرفات
        $this->assertEquals('closed', $account->fresh()->status->name);
    }

    public function test_close_in_active_state_throws_exception_if_balance_not_zero()
    {
        $account = $this->makeAccount(['balance' => 100.00]);

        $state = new ActiveState();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Account balance must be zero to close.');

        $state->close($account);
    }

    public function test_key_returns_active()
    {
        $state = new ActiveState();
        $this->assertEquals('active', $state->key());
    }
}