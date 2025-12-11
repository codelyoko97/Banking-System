<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportedTicket extends Model
{
  use HasFactory;
    protected $fillable = [
        'customer_id',
        'title',
        'message',
        'status', 
        'priority'
    ];

    public function customer() {
        return $this->belongsTo(User::class);
    }

     public function children() {
        return $this->hasMany(SupportedTicketMessage::class, 'supported_ticket_id');
    }
}
