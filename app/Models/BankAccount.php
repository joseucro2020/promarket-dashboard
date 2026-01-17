<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccount extends Model
{
    use SoftDeletes;

    public const ZELLE = 'zelle';
    public const NACIONAL = 'nacional';

    protected $table = 'bank_accounts';

    protected $fillable = [
        'name',
        'bank_id',
        'number',
        'identification',
        'method',
        'type',
        'email',
        'phone',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
        'bank_id' => 'integer',
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }
}
