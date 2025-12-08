<?php

namespace App\Banking\Transactions\Adapter;

use App\Jobs\LogJob;
use App\Models\Account;
use DomainException;
use Exception;
use Illuminate\Support\Facades\Auth;
use Stripe\Charge;
use Stripe\Payout;
use Stripe\Balance;
use Stripe\Stripe;

class StripeAdapter implements PaymentInterface
{
  public function __construct()
  {
    Stripe::setApiKey(env('STRIPE_SECRET'));
  }

  public function pay(float $amount, array $data): array
  {
    try {
      $account = Account::where('number', $data['account_id'])->first();

      if ($account->customer_id != Auth::user()->id) {
        throw new DomainException("You can't pay with this account");
      }

      $charge = Charge::create([
        'amount' => $amount * 100,
        'currency' => 'usd',
        'source' => $data['stripeToken'],
        'description' => 'Banking Payment'
      ]);

      // \Stripe\Transfer::create([
      //   'amount' => $paymentData['amount'] * 100,
      //   'currency' => 'sek',
      //   'destination' => 'SE3550000000054910000003',
      // ]);

      if ($charge->status === 'succeeded') {
        $account->balance -= $amount;
        $account->save();

        LogJob::dispatch($account->customer_id, 'stripe payment', "Pay {$amount} to Stripe");

        return [
          'success' => true,
          'payment_id' => $charge->id,
          'amount' => $amount,
          'message' => 'Payment successful'
        ];
      }
      return [
        'success' => false,
        'message' => 'Payment failed',
        'error' => 'Payment status: ' . $charge->status
      ];
    } catch (Exception $e) {
      return [
        'success' => false,
        'message' => 'Payment failed',
        'error' => $e->getMessage()
      ];
    }
  }

  public function withdraw(float $amount, array $data): array
  {
    try {
      $payout = Payout::create([
        'amount' => $amount * 100,
        'currency' => 'usd',
        'description' => 'User withdrawal request'
      ], [
        // 'stripe_account' => $data['stripe_account_id']
      ]);

      if ($payout->status === 'pending') {
        $account = Account::where('number', $data['account_id'])->first();
        $account->balance += $amount;
        $account->save();

        LogJob::dispatch($account->customer_id, 'stripe withdraw', "Withdraw {$amount} from Stripe");

        return [
          'success' => true,
          'payout_id' => $payout->id,
          'amount' => $amount,
          'message' => 'Stripe payout successful'
        ];
      }

      return [
        'success' => false,
        'message' => 'Payout not successful',
        'status' => $payout->status
      ];
    } catch (\Exception $e) {
      return [
        'success' => false,
        'message' => 'Payout failed',
        'error' => $e->getMessage()
      ];
    }
  }

  public function getBalance(): array
  {
    $balance = Balance::retrieve();
    return [
      'success' => true,
      'balance' => $balance->available[0]->amount / 100,
    ];
    // try {
    //   Stripe::setApiKey(env('STRIPE_SECRET'));
    //   $balance = Balance::retrieve();

    //   $available = array_map(function ($b) {
    //     return [
    //       'amount' => $b->amount,
    //       'currency' => $b->currency,
    //     ];
    //   }, $balance->available ?? []);

    //   $pending = array_map(function ($b) {
    //     return [
    //       'amount' => $b->amount,
    //       'currency' => $b->currency,
    //     ];
    //   }, $balance->pending ?? []);

    //   // Optional conversion to USD using .env rates like FX_SEK_USD=0.095 (1 SEK = 0.095 USD)
    //   $availableUsd = null;
    //   $pendingUsd = null;

    //   $convAvailable = 0.0;
    //   $convPending = 0.0;
    //   $canConvertAll = true;
    //   $usedAnyDefault = false;

    //   foreach ($available as $item) {
    //     $curr = strtoupper($item['currency']);
    //     $amountBase = $item['amount'] / 100.0;
    //     if ($curr === 'USD') {
    //       $convAvailable += $amountBase;
    //     } else {
    //       $usedDefault = false;
    //       $rate = $this->resolveUsdRate($curr, $usedDefault);
    //       if ($rate !== null) {
    //         $convAvailable += ($amountBase * $rate);
    //         $usedAnyDefault = $usedAnyDefault || $usedDefault;
    //       } else {
    //         $canConvertAll = false;
    //       }
    //     }
    //   }

    //   foreach ($pending as $item) {
    //     $curr = strtoupper($item['currency']);
    //     $amountBase = $item['amount'] / 100.0;
    //     if ($curr === 'USD') {
    //       $convPending += $amountBase;
    //     } else {
    //       $usedDefault = false;
    //       $rate = $this->resolveUsdRate($curr, $usedDefault);
    //       if ($rate !== null) {
    //         $convPending += ($amountBase * $rate);
    //         $usedAnyDefault = $usedAnyDefault || $usedDefault;
    //       } else {
    //         $canConvertAll = false;
    //       }
    //     }
    //   }

    //   if ($canConvertAll) {
    //     $availableUsd = round($convAvailable, 2);
    //     $pendingUsd = round($convPending, 2);
    //   }

    //   return [
    //     'balance' => $pendingUsd,
    //   ];
    // } catch (Exception $e) {
    //   return [
    //     'success' => false,
    //     'message' => 'Failed to retrieve balance',
    //     'error' => $e->getMessage(),
    //   ];
    // }
  }

  // Resolve USD conversion rate for a currency.
  // Priority: FX_<CUR>_USD -> FX_DEFAULT_USD -> built-in defaults (SEK)
  private function resolveUsdRate(string $currency, ?bool &$usedDefault = false): ?float
  {
    if (strtoupper($currency) === 'SEK') {
      $usedDefault = true;
      return 0.1;
    }

    return null;
  }
}
