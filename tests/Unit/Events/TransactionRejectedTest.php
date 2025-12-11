<?php

namespace Tests\Unit\Events;

use App\Events\TransactionRejected;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionRejectedTest extends TestCase
{
    use RefreshDatabase;

    /** ✅ يتأكد أن الـ Event يخزن نسخة الـ Transaction */
    public function test_event_stores_transaction_instance()
    {
        $transaction = Transaction::factory()->create([
            'type' => 'withdraw',
            'amount' => 300.00,
            'status' => 'rejected',
            'description' => 'Insufficient funds',
        ]);

        $event = new TransactionRejected($transaction);

        $this->assertEquals($transaction->id, $event->transaction->id);
        $this->assertEquals('withdraw', $event->transaction->type);
        $this->assertEquals(300.00, (float)$event->transaction->amount);
        $this->assertEquals('rejected', $event->transaction->status);
        $this->assertEquals('Insufficient funds', $event->transaction->description);
    }

    /** ✅ يتأكد أن الـ Event يتعامل مع Transaction فيه قيم null */
    public function test_event_handles_transaction_with_null_description()
    {
        $transaction = Transaction::factory()->make([
            'type' => 'transfer',
            'amount' => 150.00,
            'status' => 'rejected',
            'description' => null,
        ]);

        $event = new TransactionRejected($transaction);

        $this->assertEquals('transfer', $event->transaction->type);
        $this->assertEquals(150.00, (float)$event->transaction->amount);
        $this->assertEquals('rejected', $event->transaction->status);
        $this->assertNull($event->transaction->description);
    }
}