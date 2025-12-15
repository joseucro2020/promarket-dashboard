<?php

	namespace App\Models;

	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\SoftDeletes;

	class Tag extends Model {
	    protected $table = "tags";

	    use SoftDeletes;

	    protected $fillable = [
	    	'name'
	    ];
	}
