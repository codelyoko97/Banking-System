<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Services\TransactionService;

class TransactionController extends Controller
{
  protected $transactionService;
  public function __construct(TransactionService $transactionService)
  {
    $this->transactionService = $transactionService;
  }
  public function transaction(TransactionRequest $request)
  {
    return response()->json($this->transactionService->transaction($request->validated()));
  }
}
