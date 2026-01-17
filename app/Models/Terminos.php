<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Terminos extends Model
{
    protected $table = 'terminos';

    protected $fillable = [
        'texto',
        'english',
    ];
}
