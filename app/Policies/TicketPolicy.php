<?php

namespace App\Policies;

use App\Models\SupportedTicket;
use App\Models\User;

class TicketPolicy
{
    public function view(User $user, SupportedTicket $ticket)
    {
        return $user->id === $ticket->customer_id || $user->role_id ==5;
    }

    public function reply(User $user, SupportedTicket $ticket)
    {
        return $user->id === $ticket->customer_id || $user->role_id ==5;
    }

    public function updateStatus(User $user)
    {
        return $user->role_id ==5;
    }
     public function manage(User $user): bool
    {
        return $user->role_id == 5; 
    }
}
