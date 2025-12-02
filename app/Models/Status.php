<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $fillable = [
        'name'
    ];

    public function accounts() {
        $this->hasMany(Account::class);
    }
}
