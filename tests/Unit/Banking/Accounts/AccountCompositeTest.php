<?php

namespace Tests\Unit\Banking\Accounts;

use App\Banking\Accounts\AccountComposite;
use PHPUnit\Framework\TestCase;

class AccountCompositeTest extends TestCase
{
    public function test_add_and_get_children()
    {
        $composite = new AccountComposite(1);

        $child1 = $this->createMock(\App\Banking\Accounts\AccountComponent::class);
        $child1->method('getBalance')->willReturn(100.0);

        $child2 = $this->createMock(\App\Banking\Accounts\AccountComponent::class);
        $child2->method('getBalance')->willReturn(200.0);

        $composite->addChild($child1);
        $composite->addChild($child2);

        $this->assertCount(2, $composite->getChildren());
        $this->assertEquals(300.0, $composite->getBalance());
    }

    public function test_deposit_throws_exception()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot deposit directly to composite account');

        $composite = new AccountComposite(1);
        $composite->deposit(100);
    }

    public function test_withdraw_throws_exception()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot withdraw directly from composite account');

        $composite = new AccountComposite(1);
        $composite->withdraw(50);
    }
}