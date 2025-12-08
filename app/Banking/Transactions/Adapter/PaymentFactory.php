<?php

namespace App\Banking\Transactions\Adapter;

class PaymentFactory
{
  public static function make(string $driver): PaymentInterface
  {
    return match ($driver) {
      'stripe' => new StripeAdapter(),
      'braintree' => new PaypalBraintreeAdapter(),
      default => throw new \Exception("Unsupported payment driver: $driver")
    };
  }
}
