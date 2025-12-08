<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminTicketController;
use App\Http\Controllers\AiController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RecommendationController;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
  return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);

Route::middleware(['auth:sanctum'])->group(function () {
  Route::post('/logout', [AuthController::class, 'logout']);

  // Transactions
  Route::post('/transaction', [TransactionController::class, 'transaction']);
  Route::post('/transaction/{id}/approve', [TransactionController::class, 'approve']);
  Route::post('/transaction/{id}/reject', [TransactionController::class, 'reject']);
  Route::post('/scheduled-transactions', [TransactionController::class, 'store']);
  Route::get('/show-transactions', [TransactionController::class, 'showTransactions']);

  Route::post('/pay', [PaymentController::class, 'processPayment']);
  Route::post('/withdraw', [PaymentController::class, 'processWithdraw']);
  Route::post('/balance', [PaymentController::class, 'getBalance']);

  Route::get('/account/types/all', [GeneralController::class, 'getAllAccountType']);
  Route::get('/account/statuses/all', [GeneralController::class, 'getAllStatuses']);
  Route::get('/roles/all', [GeneralController::class, 'getAllRoles']);
});


Route::prefix('account')->middleware('auth:sanctum')->group(function () {
  Route::post('/', [AccountController::class, 'store']);
  Route::post('{id}', [AccountController::class, 'update']);
  Route::get('{id}/close', [AccountController::class, 'close']);
  // Route::get('{id}/balance', [AccountController::class, 'balance']);
  Route::get('{id}/full-balance', [AccountController::class, 'fullBalance']);
  Route::get('{id}/tree', [AccountController::class, 'tree']);
  Route::get('/all', [AccountController::class, 'index']);
});

// Gimini
Route::post('/ai/recommend', [AiController::class, 'recommend']);




Route::get('/poc/summary/{account}', function ($accountId, RecommendationService $svc) {
  $summary = $svc->buildAccountSummary((int)$accountId);
  return response()->json($summary);
});

Route::get('/recommend/{account}', [RecommendationController::class, 'recommend']);



Route::middleware('auth:sanctum')->group(function () {

  // Tickets
  Route::post('tickets', [TicketController::class, 'store']);
  Route::get('tickets', [TicketController::class, 'index']);
  Route::get('tickets/{id}', [TicketController::class, 'show']);
  Route::post('tickets/{id}/reply', [TicketController::class, 'reply']);

  // Staff only
  Route::post('tickets/{id}/status', [TicketController::class, 'changeStatus']);
});
