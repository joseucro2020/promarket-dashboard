<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $table = 'exchange_rates';

    protected $fillable = [
        'currency_from',
        'currency_to',
        'rate',
        'date',
        'notes'
    ];

    protected $casts = [
        'date' => 'datetime',
        'rate' => 'decimal:4'
    ];
}
