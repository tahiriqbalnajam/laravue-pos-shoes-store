<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseProducts extends Model
{
    protected $table = 'purchase_products';

    protected $fillable = [
        'id', 'purchase_id', 'product_id', 'price','quantity','bonus',
        'discount1','batch_no','discount2','exp_date'
    ];

    public function product()
    {
        return $this->belongsTo('App\Product')->select('id','name','code');

    }
    public function batches(){

        return $this->belongsTo('App\Batches','product_id','product_id')->select(array('id','product_id','batch_no','exp_date'));

    }
}
