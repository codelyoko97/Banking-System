<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;
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
  Route::post('/transactions', [TransactionController::class, 'transaction']);
});


Route::prefix('account')->middleware('auth:sanctum')->group(function () {
  Route::post('/', [AccountController::class, 'store']);
  Route::patch('{id}', [AccountController::class, 'update']);
  Route::post('{id}/close', [AccountController::class, 'close']);
  // Route::get('{id}/balance', [AccountController::class, 'balance']);
  Route::get('{id}/full-balance', [AccountController::class, 'fullBalance']);
  Route::get('{id}/tree', [AccountController::class, 'tree']);
});
