<?php

namespace App\Banking\Transactions\Adapter;

use App\Jobs\LogJob;
use App\Models\Account;
use Braintree\Gateway;
use Braintree\TransactionSearch;
use DomainException;
use Illuminate\Support\Facades\Auth;

class PaypalBraintreeAdapter implements PaymentInterface
{
  protected $gateway;

  public function __construct()
  {
    $this->gateway = new Gateway([
      'environment' => env('BRAINTREE_ENV'),
      'merchantId' => env('BRAINTREE_MERCHANT_ID'),
      'publicKey' => env('BRAINTREE_PUBLIC_KEY'),
      'privateKey' => env('BRAINTREE_PRIVATE_KEY'),
    ]);
  }

  public function pay(float $amount, array $data): array
  {
    $account = Account::where('number', $data['account_id'])->first();

    if ($account->customer_id != Auth::user()->id) {
      throw new DomainException("You can't pay with this account");
    }

    $result = $this->gateway->transaction()->sale([
      'amount' => number_format($amount, 2, '.', ''),
      'paymentMethodNonce' => $data['nonce'],
      'options' => ['submitForSettlement' => true]
    ]);

    if ($result->success) {
      $account->balance -= $amount;
      $account->save();

      LogJob::dispatch($account->customer_id, 'paypal payment', "Pay {$amount} to Paypal");

      return [
        'success' => true,
        'transactionId' => $result->transaction->id,
        'amount' => $result->transaction->amount,
      ];
    }

    return [
      'success' => false,
      'message' => $result->message
    ];
  }

  public function withdraw(float $amount, array $data): array
  {
    // return [
    //   'success' => false,
    //   'message' => 'Braintree does not support withdraw in this adapter'
    // ];

    $result = $this->gateway->transaction()->refund(
      $data['transaction_id'],
      $amount
    );

    if ($result->success) {
      $account = Account::where('number', $data['account_id'])->first();
      $account->balance += $amount;
      $account->save();

      LogJob::dispatch($account->customer_id, 'paypal withdraw', "withdraw {$amount} from Paypal");

      return [
        'success' => true,
        'refundTransactionId' => $result->transaction->id,
        'status' => $result->transaction->status,
        'amount' => $result->transaction->amount
      ];
    }

    return [
      'success' => false,
      'message' => $result->message ?? 'Refund failed'
    ];
  }

  public function getBalance(): array
  {
    $collection = $this->gateway->transaction()->search([
      TransactionSearch::status()->is(\Braintree\Transaction::SETTLED)
    ]);
    $total = 0.0;
    foreach ($collection as $transaction) {
      $total += floatval($transaction->amount);
    }

    return [
      'success' => true,
      'settled_balance_usd' => number_format($total, 2, '.', '')
    ];
  }
}
