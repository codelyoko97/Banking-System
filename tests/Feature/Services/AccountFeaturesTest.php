<?php

namespace Tests\Feature\Services;

use App\Services\Accounts\Features\AccountInsurance;
use App\Services\Accounts\Features\PremiumService;
use App\Services\Accounts\Features\AccountInterface;
use PHPUnit\Framework\TestCase;

class AccountFeaturesTest extends TestCase
{
    // كائن وهمي يطبق AccountInterface
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
    public function test_account_insurance_adds_description()
    {
        $base = $this->makeBaseAccount();
        $insurance = new AccountInsurance($base);

        $this->assertEquals("Basic Account + Insurance", $insurance->getDescription());
        $this->assertEquals(1000.0, $insurance->getBalance());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_premium_service_adds_description()
    {
        $base = $this->makeBaseAccount();
        $premium = new PremiumService($base);

        $this->assertEquals("Basic Account + Premium Service", $premium->getDescription());
        $this->assertEquals(1000.0, $premium->getBalance());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_nested_decorators_work_together()
    {
        $base = $this->makeBaseAccount();

        $insurance = new AccountInsurance($base);
        $premium = new PremiumService($insurance);

        $this->assertEquals("Basic Account + Insurance + Premium Service", $premium->getDescription());
        $this->assertEquals(1000.0, $premium->getBalance());
    }
}