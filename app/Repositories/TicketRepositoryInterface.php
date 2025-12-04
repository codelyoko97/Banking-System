<?php
namespace App\Repositories;

use App\Models\SupportedTicket;
use App\Models\SupportedTicketMessage;
use Illuminate\Support\Collection;

interface TicketRepositoryInterface
{
    public function createTicket(array $data): SupportedTicket;
    public function findTicketById(int $id): ?SupportedTicket;
    public function listUserTickets(int $userId, int $perPage = 20);
    public function listAllTickets(array $filters = [], int $perPage = 20);
    public function addMessage(int $ticketId, array $data): SupportedTicketMessage;
    public function getMessages(int $ticketId): Collection;
    public function updateTicket(int $id, array $data): SupportedTicket;
}
