<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = [
        'customer_id',
        'number',
        'type_id',
        'account_related_id',
        'balance',
        'status_id'
    ];

    public function customer() {
        return $this->belongsTo(User::class);
    }
    public function type() {
        return $this->belongsTo(Type::class);
    }
    public function related() {
        return $this->hasMany(Account::class);
    }
    public function status() {
        return $this->belongsTo(Status::class);
    }
    public function transactions() {
        return $this->hasMany(Transaction::class);
    }
    public function schedulaTransactions() {
        return $this->hasMany(SchedualeTransaction::class);
    }
}
