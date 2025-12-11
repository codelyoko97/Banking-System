<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
  use HasFactory;
    protected $fillable = [
        'account_id',
        'status',
        'amount',
        'type',
        'account_related_id',
        'role_id',
        'employee_name',
        'description'
    ];

    public function account() {
        return $this->belongsTo(Account::class);
    }
    public function related() {
        return $this->hasMany(Transaction::class);
    }
}
