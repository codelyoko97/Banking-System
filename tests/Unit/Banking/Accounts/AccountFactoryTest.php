<?php

namespace Tests\Unit\Banking\Accounts;

use App\Banking\Accounts\AccountFactory;
use App\Banking\Accounts\AccountLeaf;
use App\Banking\Accounts\AccountComposite;
use App\Models\Account;
use App\Repositories\AccountRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountFactoryTest extends TestCase
{
  use RefreshDatabase;

  public function test_build_tree_returns_leaf_for_account_without_children()
  {
    $account = Account::factory()->create();
    $repo = $this->createMock(AccountRepositoryInterface::class);

    $component = AccountFactory::buildTree($account, $repo);

    $this->assertInstanceOf(AccountLeaf::class, $component);
  }

  public function test_build_tree_returns_composite_for_account_with_children()
  {
    $parent = Account::factory()->create();
    $child = Account::factory()->create(['account_related_id' => $parent->id]);

    $parent->setRelation('children', collect([$child]));

    $repo = $this->createMock(AccountRepositoryInterface::class);
    $component = AccountFactory::buildTree($parent, $repo);

    $this->assertInstanceOf(AccountComposite::class, $component);
    $this->assertCount(1, $component->getChildren());
  }
}
