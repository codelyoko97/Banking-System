<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Models\Account;
use App\Models\SchedualeTransaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
  protected $transactionService;
  public function __construct(TransactionService $transactionService)
  {
    $this->transactionService = $transactionService;
  }
  public function transaction(TransactionRequest $request)
  {
    $dto = $request->toDTO($request->user());
    return response()->json($this->transactionService->process($dto));
  }

  public function approve($id)
  {
    return response()->json($this->transactionService->approve($id));
  }
  public function store(TransactionRequest $request)
  {
    $plan = $this->transactionService->addPlan($request->validated());

    return response()->json([
      'message' => 'Scheduled transaction created successfully',
      'data'    => $plan,
    ], 201);
  }
}
