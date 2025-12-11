<?php

namespace Tests\Feature;

use App\Services\Accounts\Features\AccountDecorator;
use App\Services\Accounts\Features\AccountInterface;
use PHPUnit\Framework\TestCase;

class AccountDecoratorTest extends TestCase
{
    private function makeBaseAccount(): AccountInterface
    {
        return new class implements AccountInterface {
            public function getDescription(): string
            {
                return "Basic Account";
            }

            public function getBalance(): float
            {
                return 1000.0;
            }
        };
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_decorator_returns_same_description_and_balance()
    {
        $base = $this->makeBaseAccount();

        $decorator = new class($base) extends AccountDecorator {};

        $this->assertEquals("Basic Account", $decorator->getDescription());
        $this->assertEquals(1000.0, $decorator->getBalance());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_custom_decorator_can_add_feature_to_description()
    {
        $base = $this->makeBaseAccount();

        $insuranceDecorator = new class($base) extends AccountDecorator {
            public function getDescription(): string
            {
                return parent::getDescription() . " + Insurance";
            }
        };

        $this->assertEquals("Basic Account + Insurance", $insuranceDecorator->getDescription());
        $this->assertEquals(1000.0, $insuranceDecorator->getBalance());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_custom_decorator_can_modify_balance()
    {
        $base = $this->makeBaseAccount();

        $premiumDecorator = new class($base) extends AccountDecorator {
            public function getBalance(): float
            {
                return parent::getBalance() + 200.0;
            }
        };

        $this->assertEquals("Basic Account", $premiumDecorator->getDescription());
        $this->assertEquals(1200.0, $premiumDecorator->getBalance());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_nested_decorators_work_together()
    {
        $base = $this->makeBaseAccount();

        $insuranceDecorator = new class($base) extends AccountDecorator {
            public function getDescription(): string
            {
                return parent::getDescription() . " + Insurance";
            }
        };

        $premiumDecorator = new class($insuranceDecorator) extends AccountDecorator {
            public function getBalance(): float
            {
                return parent::getBalance() + 500.0;
            }
        };

        $this->assertEquals("Basic Account + Insurance", $premiumDecorator->getDescription());
        $this->assertEquals(1500.0, $premiumDecorator->getBalance());
    }
}