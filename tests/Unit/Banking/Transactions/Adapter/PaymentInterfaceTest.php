<?php

namespace Tests\Unit\Banking\Transactions\Adapter;

use App\Banking\Transactions\Adapter\PaymentInterface;
use App\Banking\Transactions\Adapter\StripeAdapter;
use App\Banking\Transactions\Adapter\PaypalBraintreeAdapter;
use PHPUnit\Framework\TestCase;

class PaymentInterfaceTest extends TestCase
{
    public function test_stripe_adapter_implements_payment_interface()
    {
        $adapter = new StripeAdapter();
        $this->assertInstanceOf(PaymentInterface::class, $adapter);
    }

    public function test_braintree_adapter_implements_payment_interface()
    {
        $adapter = new PaypalBraintreeAdapter();
        $this->assertInstanceOf(PaymentInterface::class, $adapter);
    }

    public function test_stripe_adapter_methods_return_array()
    {
        $adapter = $this->createMock(StripeAdapter::class);

        $adapter->method('pay')->willReturn(['success' => true]);
        $adapter->method('withdraw')->willReturn(['success' => true]);
        $adapter->method('getBalance')->willReturn(['success' => true, 'balance' => 100]);

        $this->assertIsArray($adapter->pay(100, []));
        $this->assertIsArray($adapter->withdraw(50, []));
        $this->assertIsArray($adapter->getBalance());
    }

    public function test_braintree_adapter_methods_return_array()
    {
        $adapter = $this->createMock(PaypalBraintreeAdapter::class);

        $adapter->method('pay')->willReturn(['success' => true]);
        $adapter->method('withdraw')->willReturn(['success' => true]);
        $adapter->method('getBalance')->willReturn(['success' => true, 'settled_balance_usd' => 200]);

        $this->assertIsArray($adapter->pay(200, []));
        $this->assertIsArray($adapter->withdraw(100, []));
        $this->assertIsArray($adapter->getBalance());
    }
}