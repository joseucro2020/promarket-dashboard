<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    protected $table = 'payment_gateways';

    protected $fillable = [
        'name',
        'provider',
        'type',
        'available_types',
        'currency',
        'icon',
        'description',
        'order',
        'status',
        'config',
        'payment_method_code',
    ];

    protected $casts = [
        'status' => 'boolean',
        'order' => 'integer',
        'available_types' => 'array',
        'config' => 'array',
    ];

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_code', 'code');
    }
}
