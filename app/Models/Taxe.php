<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Taxe extends Model
{
    protected $table = "taxes";

    protected $fillable = ['name', 'description', 'percentage', 'status'];

    protected $attributes = [
        'status' => self::STATUS_ACTIVE,
    ];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
}
