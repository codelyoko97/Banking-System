<?php

namespace App\Listeners;

use App\Events\TransactionApproved;
use App\Services\NotificationAdapterInterface;

class SendNotificationOnTransactionApproved
{
  public function handle(TransactionApproved $event): void
  {
    $transaction = $event->transaction;
    app(NotificationAdapterInterface::class)
      ->sendToUser(
        $transaction->user_id,
        "Transaction approved",
        "Transaction {$transaction->id} has been approved",
        'transaction'
      );
  }
}
