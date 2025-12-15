<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $table = 'exchange_rates';

    protected $fillable = [
        'currency_from',
        'currency_to',
        'change',
        'created_at',
        'notes'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'change' => 'decimal:4'
    ];
}
