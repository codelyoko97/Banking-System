<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchedualeTransaction extends Model
{
    protected $fillable = [
        'account_id',
        'type',
        'amount',
        'frequency',
        'next_run',
        'account_related_id'
    ];

    public function account() {
        return $this->belongsTo(Account::class);
    }
    public function related() {
        return $this->hasMany(SchedualeTransaction::class);
    }
}
