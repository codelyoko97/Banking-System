<?php

namespace App\Http\Controllers;

use App\DTOs\CreateTicketDTO;
use App\DTOs\ReplyTicketDTO;
use App\Http\Requests\CreateTicketRequest;
use App\Http\Requests\ReplyTicketRequest;
use App\Models\SupportedTicket;
use App\Services\TicketService;
use Illuminate\Http\Request;

class TicketController extends Controller
{
  protected TicketService $service;

  public function __construct(TicketService $service)
  {
    $this->service = $service;
  }

  // public function store(CreateTicketRequest $req)
  // {
  //     $data = $req->validated();
  //     $data['customer_id'] = $req->user()->id;
  //     $ticket = $this->service->createTicket($data);
  //     return response()->json($ticket, 201);
  // }



  public function store(CreateTicketRequest $req)
  {
    $data = $req->validated();
    $data['customer_id'] = $req->user()->id;

    $dto = CreateTicketDTO::fromArray($data);

    $ticket = $this->service->createTicket($dto->toArray());

    return response()->json($ticket, 201);
  }


  public function index(Request $req)
  {
    if ($req->user()->role_id == 5) {
      return response()->json($this->service->listAllTickets());
    }

    return response()->json($this->service->getUserTickets($req->user()->id));
  }

  public function show(Request $req, $id)
  {
    $ticket = $this->service->getTicket($id);

    $this->authorize('view', $ticket);

    return response()->json([
      'ticket' => $ticket,
    ]);
  }

  public function reply(ReplyTicketRequest $req, $id)
  {
    $ticket = $this->service->getTicket($id);

    $this->authorize('reply', $ticket);

    $data = $req->validated();
    $data['sender_id'] = $req->user()->id;
    $data['sender_type'] = $req->user()->role_id == 5 ? 'staff' : 'user';

    // $msg = $this->service->replyTicket($id, $data);

    $dto = ReplyTicketDTO::fromArray($data);

    $msg = $this->service->replyTicket($id, $dto->toArray());

    return response()->json($msg, 201);
  }

  public function changeStatus(Request $req, $id)
  {
    $this->authorize('manage', SupportedTicket::class);

    $ticket = $this->service->changeStatus($id, $req->status);
    return response()->json($ticket);
  }
}
