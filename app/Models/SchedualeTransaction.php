<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchedualeTransaction extends Model
{
  protected $table = 'scheduale_transactions';

  protected $fillable = [
    'account_id',
    'amount',
    'type',
    'frequency',
    'account_related_id',
    'next_run',
    'active',
    'end_date',
    'day_of_month',
  ];

  protected $casts = [
    'amount' => 'decimal:4',
    'next_run' => 'datetime',
    'end_date' => 'date',
    'active' => 'boolean',
  ];

  public function account(): BelongsTo
  {
    return $this->belongsTo(Account::class);
  }

  public function relatedAccount(): BelongsTo
  {
    return $this->belongsTo(Account::class, 'account_related_id');
  }
}
