<?php

namespace App\Services;

use App\Mail\TicketReplyMail;
use App\Repositories\TicketRepositoryInterface;
use App\Models\SupportedTicket;
use App\Models\SupportedTicketMessage;
use Illuminate\Support\Facades\DB;
use App\Events\TicketCreated;
use App\Events\TicketReplied;
use App\Jobs\SendTicketReplyEmail;
use Illuminate\Support\Facades\Mail;

class TicketService
{
  protected TicketRepositoryInterface $repo;
  // protected NotificationAdapterInterface $notifier;

  public function __construct(TicketRepositoryInterface $repo)
  {
    $this->repo = $repo;
    // $this->notifier = $notifier;
  }


  public function listAllTickets(array $filters = [])
  {
    return $this->repo->listAllTickets($filters);
  }


  public function createTicket(array $data): SupportedTicket
  {
    return DB::transaction(function () use ($data) {
      $ticket = $this->repo->createTicket([
        'customer_id' => $data['customer_id'],
        'title' => $data['title'],
        'message' => $data['message'],
        'status' => $data['status'] ?? 'sended',
        'priority' => $data['priority'] ?? 'normal',
      ]);

      $this->repo->addMessage($ticket->id, [
        'sender_id' => $data['customer_id'],
        'sender_type' => 'user',
        'message' => $data['message'],
        'is_private' => false,
      ]);

      event(new TicketCreated($ticket));

      // $this->notifier->sendToStaff("New ticket #{$ticket->id}: {$ticket->title}", $ticket->customer_id);

      return $ticket;
    });
  }

  public function replyTicket(int $ticketId, array $data): SupportedTicketMessage
  {
    return DB::transaction(function () use ($ticketId, $data) {
      if (!isset($data['sender_type'])) {
        $data['sender_type'] = auth()->user()->role_id == 5
          ? 'Support Agent'
          : 'user';
      }
      $msg = $this->repo->addMessage($ticketId, [
        'sender_id' => $data['sender_id'] ?? null,
        'sender_type' => $data['sender_type'] ?? 'user',
        'message' => $data['message'],
        'is_private' => $data['is_private'] ?? false,
      ]);

      if ($data['sender_type'] === 'staff') {
        $this->repo->updateTicket($ticketId, ['status' => 'in_progress']);
      }

      event(new TicketReplied($msg));

      // if ($data['sender_type'] === 'staff') {
      //   $ticket = $this->repo->findTicketById($ticketId);

      //   dispatch(new SendTicketReplyEmail($ticket, $msg));


      // $this->notifier->sendToUser(
      //   $ticket->customer_id,
      //   "Reply on ticket #{$ticket->id}",
      //   $msg->message
      // );
      // }


      return $msg;
    });
  }

  public function changeStatus(int $ticketId, string $status): SupportedTicket
  {
    return DB::transaction(function () use ($ticketId, $status) {
      $ticket = $this->repo->updateTicket($ticketId, ['status' => $status]);
      return $ticket;
    });
  }

  public function getUserTickets(int $userId)
  {
    return $this->repo->listUserTickets($userId);
  }

  public function getTicket(int $id): ?SupportedTicket
  {
    return $this->repo->findTicketById($id);
  }

  // public function getMessages(int $ticketId)
  // {
  //   return $this->repo->getMessages($ticketId);
  // }
}
