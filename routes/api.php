<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminHealthController;
use App\Http\Controllers\AdminTicketController;
use App\Http\Controllers\AiController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\StaffController;
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
  Route::post('/transaction/{id}/approve', [TransactionController::class, 'approve'])->middleware(['auth:sanctum', 'can:approve-transaction']);;
  Route::post('/scheduled-transactions', [TransactionController::class, 'store']);

  Route::get('/account/types/all', [GeneralController::class, 'getAllAccountType']);
  Route::get('/account/statuses/all', [GeneralController::class, 'getAllStatuses']);
  Route::get('/roles/all', [GeneralController::class, 'getAllRoles']);
});


Route::prefix('account')->middleware('auth:sanctum')->group(function () {
  Route::post('/', [AccountController::class, 'store'])->middleware('can:create-account');;
  Route::post('{id}', [AccountController::class, 'update']);
  Route::get('{id}/close', [AccountController::class, 'close']);
  // Route::get('{id}/balance', [AccountController::class, 'balance']);
  Route::get('{id}/full-balance', [AccountController::class, 'fullBalance']);
  Route::get('{id}/tree', [AccountController::class, 'tree']);
  Route::get('/all', [AccountController::class, 'index']);
  Route::post('/{id}/status', [AccountController::class, 'changeStatus']);
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
  Route::post('tickets/{id}/status', [TicketController::class, 'changeStatus'])->middleware(['auth:sanctum', 'can:change-ticket-status']);;
});





// //Admin
// Route::middleware(['auth:sanctum', 'can:access-admin-dashboard'])
//   ->prefix('admin')
//   ->group(function () {

//     Route::get('/stats', [AdminDashboardController::class, 'stats']);

//     Route::get('/charts/transactions-weekly', [AdminDashboardController::class, 'weeklyTransactions']);

//     Route::get('/customers', [AdminDashboardController::class, 'customers']);

//     Route::get('/employees', [AdminDashboardController::class, 'employees']);

//     Route::get('/accounts', [AdminDashboardController::class, 'accounts']);
//   });



Route::middleware(['auth:sanctum', 'can:access-admin-dashboard'])
  ->prefix('admin')
  ->group(function () {

    Route::get('/charts/transactions-weekly', [AdminDashboardController::class, 'transactionsWeekly']);
    Route::get('/charts/transactions-status', [AdminDashboardController::class, 'transactionsStatus']);
    Route::get('/charts/accounts-monthly', [AdminDashboardController::class, 'accountsMonthly']);

    Route::get('/top/customers', [AdminDashboardController::class, 'topCustomers']);
    // Route::get('/top/merchants', [AdminDashboardController::class, 'topMerchants']);

    // Dashboard counters
    Route::get('/stats/accounts-today', [AdminDashboardController::class, 'accountsToday']);
    Route::get('/stats/transactions-24h', [AdminDashboardController::class, 'transactions24h']);

    // users
    Route::get('/users/customers', [AdminDashboardController::class, 'customers']);
    Route::get('/users/employees', [AdminDashboardController::class, 'employees']);
    Route::delete('removeuser/{id}', [StaffController::class, 'destroy']);

    // logs
    Route::get('/logs/latest', [AdminDashboardController::class, 'latestLogs']);
    Route::get('/logs', [AdminDashboardController::class, 'logs']);
    Route::get('/logs/export', [AdminDashboardController::class, 'logsExport']);
  });

Route::get('/admin/health', [AdminHealthController::class, 'health'])
  ->middleware(['auth:sanctum', 'can:access-admin-dashboard']);



Route::middleware(['auth:sanctum'])->prefix('admin/staff')->group(function () {
  Route::get('/', [StaffController::class, 'index']);
  Route::post('/', [StaffController::class, 'store']);
  Route::post('{id}/role', [StaffController::class, 'updateRole']);
});



// report
// Route::prefix('admin/reports')->middleware(['auth:sanctum','can:download-reports'])->group(function () {

//     // Daily
//     Route::get('transactions/daily/pdf', [ReportsController::class, 'dailyPDF']);
//     Route::get('transactions/daily/excel', [ReportsController::class, 'dailyExcel']);

//     // Weekly
//     Route::get('transactions/weekly/pdf', [ReportsController::class, 'weeklyPDF']);
//     Route::get('transactions/weekly/excel', [ReportsController::class, 'weeklyExcel']);

//     // Monthly
//     Route::get('transactions/monthly/pdf', [ReportsController::class, 'monthlyPDF']);
//     Route::get('transactions/monthly/excel', [ReportsController::class, 'monthlyExcel']);
// });

Route::middleware(['auth:sanctum', 'can:access-admin-dashboard'])->group(function () {
  Route::get('/reports/transactions', [ReportsController::class, 'index']);
  Route::get('/reports/account-summaries', [ReportsController::class, 'accountSummaries']);
});
