<?php

namespace App\Http\Controllers;

use App\Services\AdminDashboardService;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
  public function __construct(private AdminDashboardService $svc) {}

  // Charts
  public function transactionsWeekly()
  {
    return response()->json($this->svc->transactionsWeekly());
  }
  public function transactionsStatus()
  {
    return response()->json($this->svc->transactionStatusCounts());
  }
  public function accountsMonthly(Request $req)
  {
    return response()->json($this->svc->accountsMonthly($req->query('days', 30)));
  }

  // Top lists
  public function topCustomers(Request $req)
  {
    return response()->json($this->svc->topCustomers($req->query('limit', 10)));
  }


  // Dashboard counters
  public function accountsToday()
  {
    return response()->json($this->svc->accountsToday());
  }
  public function transactions24h()
  {
    return response()->json($this->svc->transactions24h());
  }

  // Users
  public function customers()
  {
    return response()->json($this->svc->getAllCustomers());
  }
  public function employees()
  {
    return response()->json($this->svc->getAllEmployees());
  }

  // Logs
  // public function latestLogs()
  // {
  //   return response()->json($this->svc->getLatestLogs());
  // }

  public function logs(Request $req)
  {
    return response()->json(
      $this->svc->logs(
        [
          'user_id'   => $req->query('user_id'),
          'action'    => $req->query('action'),
          'date_from' => $req->query('date_from'),
          'date_to'   => $req->query('date_to'),
        ],
        $req->query('per_page', 20)
      )
    );
  }

  public function logsExport(Request $req)
  {
    $csv = $this->svc->exportLogs([
      'user_id'   => $req->query('user_id'),
      'action'    => $req->query('action'),
      'date_from' => $req->query('date_from'),
      'date_to'   => $req->query('date_to'),
    ]);

    return response($csv, 200, [
      "Content-Type" => "text/csv",
      "Content-Disposition" => "attachment; filename=logs.csv"
    ]);
  }
}
