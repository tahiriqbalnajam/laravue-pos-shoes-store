<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WheatCart extends Model
{
    protected $table = 'wheat_cart';
    public $timestamps = false;

    public function relevant_product(){
    	
    	return $this->belongsTo(Wheat::class);
    }
}
