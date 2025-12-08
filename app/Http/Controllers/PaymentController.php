<?php

namespace App\Http\Controllers;

use App\Banking\Transactions\Adapter\PaymentFactory;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
  public function processPayment(Request $request)
  {
    $payment = PaymentFactory::make($request->driver);

    $result = $payment->pay(
      amount: $request->amount,
      data: $request->all()
    );
    
    return response()->json($result);
  }

  public function processWithdraw(Request $request)
  {
    $payment = PaymentFactory::make($request->driver);

    $result = $payment->withdraw(
      amount: $request->amount,
      data: $request->all()
    );
    return response()->json($result);
  }

  public function getBalance(Request $request)
  {
    $payment = PaymentFactory::make($request->driver);
    $result = $payment->getBalance();
    return response()->json($result);
  }
}
