<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\PurchaseProducts;
class Purchase extends Model
{
    protected $table = "purchases";
    protected $fillable = [
        'supplier_id','bill_discount', 'discount_type', 'quantity','total_items', 'total_amount',
        'paid_amount','purchase_type','previous_balance','created_at'
    ];
    public function products()
    {
        return $this->hasMany('App\PurchaseProducts')
                    ->select('id','purchase_id','product_id','quantity','price','discount1','bonus','discount2')
                    ->with('product');

    }
    public function supplier()
    {
        return $this->belongsTo('App\Accounts')->select(array('id', 'name'));

    }
    public function batchs() {
        return $this->hasMany('App/ProductExpiries');
    }
}
