<?php

namespace App\Listeners;

use App\Events\TransactionRejected;
use App\Services\NotificationAdapterInterface;

class SendNotificationOnTransactionRejected
{
  public function handle(TransactionRejected $event): void
  {
    $transaction = $event->transaction;
    app(NotificationAdapterInterface::class)
      ->sendToUser(
        $transaction->user_id,
        "Transaction rejected",
        "Transaction {$transaction->id} has been rejected ",
        'transaction'
      );
  }
}
