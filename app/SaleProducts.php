<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SaleProducts extends Model
{
    protected $table = 'sale_products';

    protected $fillable = [
        'bill_discount','id', 'sale_id', 'product_id','purchase_price', 'price','quantity','bonus','discount1','discount2','batch_number','discount_type',
    ];

    public function product()
    {
        return $this->belongsTo('App\Product')->select('id','name', 'code');

    }
    public function batches(){

        return $this->hasMany('App\Batch')->select(array('id','product_id','batch_no','exp_date'));

    }
}
