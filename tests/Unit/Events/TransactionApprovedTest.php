<?php

namespace Tests\Unit\Events;

use App\Events\TransactionApproved;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionApprovedTest extends TestCase
{
    use RefreshDatabase;

    /** ✅ يتأكد أن الـ Event يخزن نسخة الـ Transaction */
    public function test_event_stores_transaction_instance()
    {
        $transaction = Transaction::factory()->create([
            'type' => 'deposit',
            'amount' => 150.00,
            'status' => 'completed',
        ]);

        $event = new TransactionApproved($transaction);

        $this->assertEquals($transaction->id, $event->transaction->id);
        $this->assertEquals('deposit', $event->transaction->type);
        $this->assertEquals(150.00, (float)$event->transaction->amount);
        $this->assertEquals('completed', $event->transaction->status);
    }

    /** ✅ يتأكد أن الـ Event يتعامل مع Transaction فيه قيم null */
    public function test_event_handles_transaction_with_null_description()
    {
        $transaction = Transaction::factory()->make([
            'type' => 'transfer',
            'amount' => 200.00,
            'status' => 'pending',
            'description' => null,
        ]);

        $event = new TransactionApproved($transaction);

        $this->assertEquals('transfer', $event->transaction->type);
        $this->assertEquals(200.00, (float)$event->transaction->amount);
        $this->assertEquals('pending', $event->transaction->status);
        $this->assertNull($event->transaction->description);
    }
}