<?php
namespace App\Repositories;

use App\Models\SupportedTicket;
use App\Models\SupportedTicketMessage;
use Illuminate\Support\Collection;

class EloquentTicketRepository implements TicketRepositoryInterface
{
    public function createTicket(array $data): SupportedTicket
    {
        return SupportedTicket::create($data);
    }

    public function findTicketById(int $id): ?SupportedTicket
    {
        return SupportedTicket::with(['customer', 'children', 'children.sender'])->find($id);
    }

    public function listUserTickets(int $userId, int $perPage = 20)
    {
        return SupportedTicket::where('customer_id', $userId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function listAllTickets(array $filters = [], int $perPage = 20)
    {
        $q = SupportedTicket::query()->orderByDesc('created_at');

        if (!empty($filters['status'])) {
            $q->where('status', $filters['status']);
        }
        if (!empty($filters['priority'])) {
            $q->where('priority', $filters['priority']);
        }

        return $q->get();
    }

    public function addMessage(int $ticketId, array $data): SupportedTicketMessage
    {
        $data['supported_ticket_id'] = $ticketId;
        return SupportedTicketMessage::create($data);
    }

    public function getMessages(int $ticketId): Collection
    {
        return SupportedTicketMessage::where('supported_ticket_id', $ticketId)
            ->orderBy('created_at')
            ->get();
    }

    public function updateTicket(int $id, array $data): SupportedTicket
    {
        $ticket = SupportedTicket::findOrFail($id);
        $ticket->update($data);
        return $ticket->fresh();
    }
}
