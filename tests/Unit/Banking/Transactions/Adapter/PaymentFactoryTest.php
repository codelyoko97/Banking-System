<?php

namespace Tests\Unit\Banking\Transactions\Adapter;

use App\Banking\Transactions\Adapter\PaymentFactory;
use App\Banking\Transactions\Adapter\StripeAdapter;
use App\Banking\Transactions\Adapter\PaypalBraintreeAdapter;
use App\Banking\Transactions\Adapter\PaymentInterface;
use PHPUnit\Framework\TestCase;

class PaymentFactoryTest extends TestCase
{
    public function test_make_returns_stripe_adapter()
    {
        $adapter = PaymentFactory::make('stripe');
        $this->assertInstanceOf(StripeAdapter::class, $adapter);
        $this->assertInstanceOf(PaymentInterface::class, $adapter);
    }

    public function test_make_returns_braintree_adapter()
    {
        $adapter = PaymentFactory::make('braintree');
        $this->assertInstanceOf(PaypalBraintreeAdapter::class, $adapter);
        $this->assertInstanceOf(PaymentInterface::class, $adapter);
    }

    public function test_make_throws_exception_for_invalid_driver()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unsupported payment driver: invalid');
        
        PaymentFactory::make('invalid');
    }
}