<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Social extends Model
{
    use HasFactory;

    // Compatibilidad con la BD/versión anterior
    protected $table = 'social_networks';

    protected $fillable = [
        'facebook',
        'youtube',
        'instagram',
        'slogan',
        'english_slogan',
        'address',
        'phone',
        'email',
    ];
}
