<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    public function products()
    {
        return $this->hasMany('App\SaleProducts','sale_id')->with('product','product.uom','product.category');

    }

    public function customer()
    {
        return $this->belongsTo('App\Accounts')->select(array('id', 'name','address'));

    }

    public function saleman()
    {
        return $this->belongsTo('App\Accounts', 'saleman_id')->select(array('id', 'name'))->withDefault(['name' => 'none']);

    }
    public function batches(){

        return $this->hasMany('App\Batch')->select(array('id','product_id','batch_no','exp_date'));

    }
}
