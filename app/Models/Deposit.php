<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    protected $table = 'deposits';

    protected $guarded = [];

    protected $casts = [
        'gateway' => 'array',
        'account' => 'array',
        'fields' => 'array',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function user()
    {
        return $this->belongsTo('\App\User', 'user_id');
    }
}
