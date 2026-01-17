<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $table = 'banks';

    protected $fillable = [
        'name',
    ];

    public function accounts()
    {
        return $this->hasMany(BankAccount::class, 'bank_id');
    }
}
