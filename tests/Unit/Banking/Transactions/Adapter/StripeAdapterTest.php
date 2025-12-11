<?php

namespace Tests\Unit\Banking\Transactions\Adapter;

use App\Banking\Transactions\Adapter\StripeAdapter;
use App\Banking\Transactions\Adapter\PaymentInterface;
use DomainException;
use PHPUnit\Framework\TestCase;

class StripeAdapterTest extends TestCase
{
    public function test_stripe_adapter_implements_interface()
    {
        $adapter = new StripeAdapter();
        $this->assertInstanceOf(PaymentInterface::class, $adapter);
    }

    public function test_pay_success()
    {
        $adapter = $this->createMock(StripeAdapter::class);
        $adapter->method('pay')->willReturn([
            'success' => true,
            'payment_id' => 'pi_test',
            'amount' => 100,
            'message' => 'Payment successful'
        ]);

        $result = $adapter->pay(100, ['stripeToken' => 'tok_test']);
        $this->assertTrue($result['success']);
        $this->assertEquals(100, $result['amount']);
    }

    public function test_pay_fails()
    {
        $adapter = $this->createMock(StripeAdapter::class);
        $adapter->method('pay')->willReturn([
            'success' => false,
            'message' => 'Payment failed',
            'error' => 'Invalid token'
        ]);

        $result = $adapter->pay(100, ['stripeToken' => 'bad_token']);
        $this->assertFalse($result['success']);
    }

    public function test_pay_throws_domain_exception()
    {
        $adapter = $this->createMock(StripeAdapter::class);
        $adapter->method('pay')->willThrowException(new DomainException("You can't pay with this account"));

        $this->expectException(DomainException::class);
        $adapter->pay(100, ['account_id' => 'wrong_account', 'stripeToken' => 'tok_test']);
    }

    public function test_withdraw_success()
    {
        $adapter = $this->createMock(StripeAdapter::class);
        $adapter->method('withdraw')->willReturn([
            'success' => true,
            'payout_id' => 'po_test',
            'amount' => 50,
            'message' => 'Stripe payout successful'
        ]);

        $result = $adapter->withdraw(50, ['account_id' => 'acc_test']);
        $this->assertTrue($result['success']);
        $this->assertEquals(50, $result['amount']);
    }

    public function test_withdraw_fails()
    {
        $adapter = $this->createMock(StripeAdapter::class);
        $adapter->method('withdraw')->willReturn([
            'success' => false,
            'message' => 'Payout not successful',
            'status' => 'failed'
        ]);

        $result = $adapter->withdraw(50, ['account_id' => 'acc_test']);
        $this->assertFalse($result['success']);
    }

    public function test_get_balance_success()
    {
        $adapter = $this->createMock(StripeAdapter::class);
        $adapter->method('getBalance')->willReturn([
            'success' => true,
            'balance' => 1000
        ]);

        $result = $adapter->getBalance();
        $this->assertTrue($result['success']);
        $this->assertIsNumeric($result['balance']);
    }

    public function test_get_balance_fails()
    {
        $adapter = $this->createMock(StripeAdapter::class);
        $adapter->method('getBalance')->willReturn([
            'success' => false,
            'message' => 'Failed to retrieve balance',
            'error' => 'API error'
        ]);

        $result = $adapter->getBalance();
        $this->assertFalse($result['success']);
    }
}