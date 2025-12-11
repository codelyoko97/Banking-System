<?php

namespace Tests\Unit\Banking\Transactions\Adapter;

use App\Banking\Transactions\Adapter\PaypalBraintreeAdapter;
use App\Banking\Transactions\Adapter\PaymentInterface;
use DomainException;
use PHPUnit\Framework\TestCase;

class PaypalBraintreeAdapterTest extends TestCase
{
    public function test_braintree_adapter_implements_interface()
    {
        $adapter = new PaypalBraintreeAdapter();
        $this->assertInstanceOf(PaymentInterface::class, $adapter);
    }

    public function test_pay_success()
    {
        $adapter = $this->createMock(PaypalBraintreeAdapter::class);
        $adapter->method('pay')->willReturn([
            'success' => true,
            'transactionId' => 'txn_test',
            'amount' => 200
        ]);

        $result = $adapter->pay(200, ['nonce' => 'fake-nonce', 'account_id' => 'acc_test']);
        $this->assertTrue($result['success']);
        $this->assertEquals(200, $result['amount']);
    }

    public function test_pay_fails()
    {
        $adapter = $this->createMock(PaypalBraintreeAdapter::class);
        $adapter->method('pay')->willReturn([
            'success' => false,
            'message' => 'Invalid nonce'
        ]);

        $result = $adapter->pay(200, ['nonce' => 'bad-nonce', 'account_id' => 'acc_test']);
        $this->assertFalse($result['success']);
    }

    public function test_pay_throws_domain_exception()
    {
        $adapter = $this->createMock(PaypalBraintreeAdapter::class);
        $adapter->method('pay')->willThrowException(new DomainException("You can't pay with this account"));

        $this->expectException(DomainException::class);
        $adapter->pay(200, ['nonce' => 'fake-nonce', 'account_id' => 'wrong_account']);
    }

    public function test_withdraw_success()
    {
        $adapter = $this->createMock(PaypalBraintreeAdapter::class);
        $adapter->method('withdraw')->willReturn([
            'success' => true,
            'refundTransactionId' => 'refund_test',
            'status' => 'settled',
            'amount' => 50
        ]);

        $result = $adapter->withdraw(50, ['transaction_id' => 'txn_test', 'account_id' => 'acc_test']);
        $this->assertTrue($result['success']);
        $this->assertEquals(50, $result['amount']);
    }

    public function test_withdraw_fails()
    {
        $adapter = $this->createMock(PaypalBraintreeAdapter::class);
        $adapter->method('withdraw')->willReturn([
            'success' => false,
            'message' => 'Refund failed'
        ]);

        $result = $adapter->withdraw(50, ['transaction_id' => 'txn_test', 'account_id' => 'acc_test']);
        $this->assertFalse($result['success']);
    }

    public function test_get_balance_success()
    {
        $adapter = $this->createMock(PaypalBraintreeAdapter::class);
        $adapter->method('getBalance')->willReturn([
            'success' => true,
            'settled_balance_usd' => 500.00
        ]);

        $result = $adapter->getBalance();
        $this->assertTrue($result['success']);
        $this->assertIsNumeric($result['settled_balance_usd']);
    }

    public function test_get_balance_empty()
    {
        $adapter = $this->createMock(PaypalBraintreeAdapter::class);
        $adapter->method('getBalance')->willReturn([
            'success' => true,
            'settled_balance_usd' => 0.00
        ]);

        $result = $adapter->getBalance();
        $this->assertTrue($result['success']);
        $this->assertEquals(0.00, $result['settled_balance_usd']);
    }
}