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
        'status_id',
        'created_at', 'updated_at'
    ];

    protected $casts = [
        'balance' => 'decimal:4'
    ];

    public function customer() {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function type() {
        return $this->belongsTo(Type::class, 'type_id');
    }

    public function children() {
        return $this->hasMany(Account::class, foreignKey: 'account_related_id');
    }

    public function parent() {
        return $this->belongsTo(Account::class, 'account_related_id');
    }

    public function status() {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function transactions() {
        return $this->hasMany(Transaction::class);
    }

    public function schedulaTransactions() {
        return $this->hasMany(SchedualeTransaction::class);
    }
}
