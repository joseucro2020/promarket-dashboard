<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
	const PAYMENT_TRANSFER = '1';
	const PAYMENT_MOBILE = '2';
	const PAYMENT_ZELLE = '3';
	const PAYMENT_PAYPAL = '4';
	const PAYMENT_EFECTIVO = '5';
	const PAYMENT_STRIPE = '6';
	const PAYMENT_BALANCE = '7';

	const STATUS_ONHOLD = 0;
	const STATUS_PROCESSING = 1;
	const STATUS_REJECTED = 2;
	const STATUS_COMPLETED = 3;

	const TURN_MORNING = 1;
	const TURN_AFTERNOON = 2;
	const TURN_NIGHT = 3;

	protected $table = "purchases";
	protected $appends = ['text_payment_type', 'status_text'];

	const CLASS_VALUE = 2;
	const CLASS_NAME = 'App\\Models\\Purchase';

	public function replenishment()
	{
		return $this->belongsTo('App\\Models\\Replenishment', 'purchase_id');
	}

	public function user()
	{
		return $this->belongsTo(\App\User::class);
	}

	public function details()
	{
		return $this->hasMany('App\\Models\\PurchaseDetails', 'purchase_id');
	}

	public function exchange()
	{
		return $this->belongsTo('App\\Models\\ExchangeRate', 'exchange_rate_id');
	}

	public function transfer()
	{
		return $this->belongsTo('App\\Models\\Transfer', 'transfer_id');
	}

	public function delivery()
	{
		return $this->hasOne('App\\Models\\PurchaseDelivery', 'purchase_id');
	}

	public function coupon()
	{
		return $this->belongsTo('App\\Models\\Coupon', 'coupon_id');
	}

	public function payments()
	{
		return $this->hasMany(PurchasePayment::class);
	}

	public function deposits()
	{
		return $this->hasMany(Deposit::class);
	}

	public function getTotalPaidAttribute()
	{
		return $this->payments()->completed()->sum('amount');
	}

	public function getRemainingAmountAttribute()
	{
		return $this->total - $this->total_paid;
	}

	public function isFullyPaid()
	{
		return $this->remaining_amount <= 0;
	}

	public function getTextPaymentTypeAttribute()
	{
		if ($this->payment_type == '1') {
			return 'Transferencia';
		} else if ($this->payment_type == '2') {
			return 'Pago Movil';
		} else if ($this->payment_type == '3') {
			return 'Zelle';
		} else if ($this->payment_type == '4') {
			return 'Paypal';
		} else if ($this->payment_type == '5') {
			return 'Efectivo';
		} else if ($this->payment_type == '7') {
			return 'Saldo';
		} else if ($this->payment_type == '8') {
			return 'USDT';
		} else if ($this->payment_type == '9') {
			return 'CASHEA';
		} else {
			return 'Stripe';
		}
	}

	public function getStatusTextAttribute()
	{
		switch ($this->status) {
			case $this::STATUS_ONHOLD:
				return 'En espera';
				break;
			case $this::STATUS_PROCESSING:
				return 'Aprobada';
				break;
			case $this::STATUS_REJECTED:
				return 'Rechazada';
				break;
			case $this::STATUS_COMPLETED:
				return 'Completada';
				break;
			default:
				return 'En espera';
				break;
		}
	}
}
