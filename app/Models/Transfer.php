<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $table = 'transfers';

    const TRANSFER_TYPE = 1;
    const PAGOMOVIL_TYPE = 2;
    const ZELLE_TYPE = 3;
    const STRIPE_TYPE = 4;

    protected $fillable = [
        'payment_type',
        'currency',
        'exchange',
        'gateway',
        'account',
        'fields',
    ];

    protected $casts = [
        'exchange' => 'float',
        'gateway' => 'array',
        'account' => 'array',
        'fields' => 'array',
    ];

    public function to()
    {
        return $this->belongsTo(BankAccount::class, 'to_bank_id');
    }

    public function from()
    {
        return $this->belongsTo(Bank::class, 'from_bank_id');
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'bank_id')->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
