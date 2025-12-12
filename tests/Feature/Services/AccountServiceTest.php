<?php

namespace Tests\Feature\Services;

use App\Models\Account;
use App\Models\Status;
use App\Models\Type;
use App\Repositories\AccountRepositoryInterface;
use App\Services\Accounts\AccountService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;
use Tests\Helpers\DisableLogs;

class AccountServiceTest extends TestCase
{
  use DatabaseTransactions;
  use DisableLogs;

  protected $repo;
  protected $service;

  protected function setUp(): void
  {
    parent::setUp();

    // تعطيل تسجيل الـ Logs إن لزم
    if (method_exists($this, 'disableLogs')) {
      $this->disableLogs();
    }

    // استبدال AccountStateFactory أثناء الاختبار فقط
    $this->app->bind(
      \App\Banking\Transactions\States\AccountStateFactory::class,
      function () {
        return new class {
          public function make($acc)
          {
            return new class {
              public function close($acc)
              {
                // لا تفعل شيئاً — فقط مرر
                return true;
              }
            };
          }
        };
      }
    );

    // Repository Mock
    $this->repo = Mockery::mock(AccountRepositoryInterface::class);
    $this->service = new AccountService($this->repo);

    Cache::flush();
  }

  public function tearDown(): void
  {
    Mockery::close();
    parent::tearDown();
  }

  public function test_create_account_with_type_name()
  {
    $type = Type::factory()->create(['name' => 'savings']);
    Status::factory()->create(['name' => 'active']);

    $this->repo->shouldReceive('create')->once()->andReturn(
      Account::factory()->make([
        'id' => 1,
        'customer_id' => 10,
        'type_id'    => $type->id,
        'status_id'  => Status::where('name', 'active')->first()->id,
        'number'     => 'AC1234',
        'balance'    => 100,
        'name'       => 'My Account',
      ])
    );

    $acc = $this->service->create([
      'type_name'   => 'savings',
      'customer_id' => 10,
      'balance'     => 100,
      'name'        => 'My Account',
    ]);

    $this->assertEquals('AC1234', $acc->number);
    $this->assertEquals('My Account', $acc->name);
    $this->assertEquals($type->id, $acc->type_id);
  }

  public function test_update_account()
  {
    $acc = Account::factory()->make([
      'id' => 1,
      'customer_id' => 10,
      'number' => 'AC1234'
    ]);

    $this->repo->shouldReceive('find')->with(1)->andReturn($acc);
    $this->repo->shouldReceive('update')->with($acc, ['account_related_id' => 5])->andReturn($acc);

    $updated = $this->service->update(1, ['account_related_id' => 5]);

    $this->assertEquals(1, $updated->id);
    $this->assertEquals('AC1234', $updated->number);
  }

  public function test_update_account_not_found_throws_exception()
  {
    $this->repo->shouldReceive('find')->with(999)->andReturn(null);

    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Account not found');

    $this->service->update(999, []);
  }

  // public function test_close_account()
  // {
  //   $acc = Account::factory()->make(['id' => 1, 'number' => 'AC1234']);

  //   $this->repo->shouldReceive('find')->with(1)->andReturn($acc);

  //   // fresh يرجع نفس الموديل
  //   $acc = Mockery::mock($acc)->makePartial();
  //   $acc->shouldReceive('fresh')->andReturn($acc);

  //   $closed = $this->service->close(1);

  //   $this->assertEquals($acc, $closed);
  // }

  public function test_get_balance_recursive()
  {
    $model = Account::factory()->make(['id' => 1, 'balance' => 500]);

    $this->repo->shouldReceive('getFullTree')->with(1)->once()->andReturn($model);

    $result = $this->service->getBalanceRecursive(1);

    $this->assertIsFloat($result);
    $result2 = $this->service->getBalanceRecursive(1);
    $this->assertEquals($result, $result2);
  }

  public function test_get_account_tree_structured_returns_array()
  {
    $child = Account::factory()->make(['id' => 2, 'number' => 'ACCHILD', 'balance' => 50]);
    $root  = Account::factory()->make(['id' => 1, 'number' => 'ACROOT',  'balance' => 250]);

    $root->setRelation('type', (object)['name' => 'savings']);
    $root->setRelation('status', (object)['name' => 'active']);
    $child->setRelation('type', (object)['name' => 'savings']);
    $child->setRelation('status', (object)['name' => 'active']);

    $root->setRelation('children', collect([$child]));
    $child->setRelation('children', collect([]));

    $this->repo->shouldReceive('getFullTree')->with(1)->once()->andReturn($root);

    $tree = $this->service->getAccountTreeStructured(1);

    $this->assertIsArray($tree);
    $this->assertArrayHasKey('children', $tree);
    $this->assertCount(1, $tree['children']);
    $this->assertEquals('ACROOT', $tree['number']);
  }

  public function test_list_accounts_for_user_and_filter_by_status()
  {
    $user = (object)['id' => 10, 'role_id' => 6];
    $list = collect([Account::factory()->make(['id' => 1])]);

    $this->repo->shouldReceive('listByCustomer')->with(10)->once()->andReturn($list);
    $this->repo->shouldReceive('filterByStatus')->with('active')->once()->andReturn($list);

    $r1 = $this->service->listAccountsForUser($user);
    $r2 = $this->service->filterByStatus('active');

    $this->assertCount(1, $r1);
    $this->assertCount(1, $r2);

    $r1b = $this->service->listAccountsForUser($user);
    $r2b = $this->service->filterByStatus('active');
    $this->assertCount(1, $r1b);
    $this->assertCount(1, $r2b);
  }

  public function test_change_status()
  {
    $acc = Account::factory()->make(['id' => 1, 'number' => 'AC1234']);
    Status::factory()->create(['name' => 'inactive']);

    $this->repo->shouldReceive('find')->with(1)->once()->andReturn($acc);
    $this->repo->shouldReceive('setStatus')->with($acc, Mockery::type('int'))->once()->andReturn($acc);

    $updated = $this->service->changeStatus(1, 'inactive');

    $this->assertEquals(1, $updated->id);
    $this->assertEquals('AC1234', $updated->number);
  }

  public function test_change_status_throws_when_invalid_account()
  {
    $this->repo->shouldReceive('find')->with(999)->once()->andReturn(null);

    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Account not found');

    $this->service->changeStatus(999, 'active');
  }

  public function test_change_status_throws_when_invalid_status_name()
  {
    $acc = Account::factory()->make(['id' => 1, 'number' => 'AC1234']);
    $this->repo->shouldReceive('find')->with(1)->once()->andReturn($acc);

    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Invalid status name');

    $this->service->changeStatus(1, 'does-not-exist');
  }


  public function test_get_decorated_account_with_features()
  {
    $acc = (object)['id' => 1, 'owner_name' => 'John', 'balance' => 100];

    $this->repo->shouldReceive('getAccountById')->with(1)->once()->andReturn($acc);
    $this->repo->shouldReceive('getFeatures')->with(1)->once()->andReturn(['overdraft', 'insurance', 'premium']);

    $dto = $this->service->getDecoratedAccount(1);

    $this->assertEquals(1, $dto->id);
    $this->assertIsString($dto->description);
    $this->assertIsFloat($dto->balance);
  }
}
