<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportedTicketMessage extends Model
{
    protected $fillable = [
        'supported_ticket_id',
        'sender_id',
        'sender_type',
        'message',
        'is_private',
    ];

    public function ticket()
    {
        return $this->belongsTo(SupportedTicket::class, 'supported_ticket_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
