<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Inventories extends Model
{
    protected $table = 'inventories';

    protected $fillable = [
        'outlet_id', 'product_id', 'quantity', 'inventory_type', 'remarks'
    ];

    public function outlet() {
        return $this->belongsTo('App\Outlet');
    }
}