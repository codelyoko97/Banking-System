<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\TicketService;
use Illuminate\Http\Request;

class AdminTicketController extends Controller
{
  protected TicketService $service;

  public function __construct(TicketService $service)
  {
    // $this->middleware('auth:sanctum');
    // $this->middleware('role:staff');
    $this->service = $service;
  }

  public function index()
  {
    return response()->json($this->service->listAllTickets());
  }


  public function show($id)
  {
    return response()->json([
      'ticket' => $this->service->getTicket($id),
      // 'messages' => $this->service->getMessages($id),
    ]);
  }

  public function reply(Request $req, $id)
  {
    $msg = $this->service->replyTicket($id, [
      'sender_id' => auth()->id(),
      'sender_type' => 'staff',
      'message' => $req->message,
    ]);

    return response()->json($msg);
  }

  public function changeStatus(Request $req, $id)
  {
    $ticket = $this->service->changeStatus($id, $req->status);
    return response()->json($ticket);
  }
}
