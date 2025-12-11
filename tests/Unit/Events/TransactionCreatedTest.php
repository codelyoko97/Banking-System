<?php

namespace Tests\Unit\Events;

use App\Events\TransactionCreated;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionCreatedTest extends TestCase
{
    use RefreshDatabase;

    /** ✅ يتأكد أن الـ Event يخزن نسخة الـ Transaction */
    public function test_event_stores_transaction_instance()
    {
        $transaction = Transaction::factory()->create([
            'type' => 'deposit',
            'amount' => 250.00,
            'status' => 'completed',
        ]);

        $event = new TransactionCreated($transaction);

        $this->assertEquals($transaction->id, $event->transaction->id);
        $this->assertEquals('deposit', $event->transaction->type);
        $this->assertEquals(250.00, (float)$event->transaction->amount);
        $this->assertEquals('completed', $event->transaction->status);
    }

    /** ✅ يتأكد أن الـ Event يتعامل مع Transaction فيه قيم null */
    public function test_event_handles_transaction_with_null_description()
    {
        $transaction = Transaction::factory()->make([
            'type' => 'transfer',
            'amount' => 500.00,
            'status' => 'pending',
            'description' => null,
        ]);

        $event = new TransactionCreated($transaction);

        $this->assertEquals('transfer', $event->transaction->type);
        $this->assertEquals(500.00, (float)$event->transaction->amount);
        $this->assertEquals('pending', $event->transaction->status);
        $this->assertNull($event->transaction->description);
    }
}