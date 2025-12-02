<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportedTicket extends Model
{
    protected $fillable = [
        'customer_id',
        'title',
        'message'
    ];

    public function customer() {
        return $this->belongsTo(User::class);
    }
}
