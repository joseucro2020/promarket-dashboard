<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pais extends Model
{
	const COUNTRY_VE = 95;

	protected $table = "paises";

	const VENEZUELA_ID = 95;

	public function estados()
	{
		return $this->hasMany('App\\Models\\Estado', 'pais_id');
	}
}
