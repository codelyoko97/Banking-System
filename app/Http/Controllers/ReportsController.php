<?php

namespace App\Http\Controllers;

use App\Services\ReportsService;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
  public function __construct(private ReportsService $reports) {}

  public function index(Request $req)
  {
    $range = $req->query('range', 'daily'); // daily/weekly/monthly
    $dto = $this->reports->getByRange($range);

    return response()->json([
      'status' => true,
      'range'  => $range,
      'data'   => $dto->data
    ]);
  }

  public function accountSummaries()
  {
    $data = $this->reports->accountSummaries();
    return response()->json($data);
  }
}
