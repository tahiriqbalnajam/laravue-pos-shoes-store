<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Wheat extends Model
{
    protected $table = 'wheat';
    
    public function cart_products() { 
        return $this->hasMany('App\WheatCart', 'wheat_id');
    }
}
