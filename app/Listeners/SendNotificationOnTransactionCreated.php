<?php

namespace App\Listeners;

use App\Events\TransactionCreated;
use App\Services\NotificationAdapterInterface;

class SendNotificationOnTransactionCreated
{
  public function handle(TransactionCreated $event): void
  {
    $transaction = $event->transaction;
    app(NotificationAdapterInterface::class)
      ->sendToUser(
        $transaction->user_id,
        "New transaction",
        "Type : {$transaction->type}, amount: {$transaction->amount}",
        'transaction'
      );
  }
}
