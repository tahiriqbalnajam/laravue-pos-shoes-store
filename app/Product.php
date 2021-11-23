<?php

namespace App;
use DB;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name', 'code', 'category_id', 'variable_product_id', 'purchase_price', 'sale_price','wholesale_price', 'status','uom_id','manufacturer_id'
    ];

    public function category() {
        return $this->belongsTo('App\ProductCategory', 'category_id')->select('id','title');
    }
    public function manufacturer() {
        return $this->belongsTo('App\Manufacturer', 'manufacture_id')->select('id','name');
    }

    public function uom() {
        return $this->belongsTo('App\Uom', 'uom_id')->select('id','name');
    }
    
    public function inventory() {
        return $this->hasMany('App\Inventories', 'product_id');
    }

    public function purchase() { 
        return $this->hasMany('App\Inventories', 'product_id')
            ->whereIn('inventory_type',['manual', 'sale_return', 'purchase']);
    }

    public function price()
    {
        return $this->hasOne(CartProducts::class, 'cart_id')
            ->join('products as p', 'product_id', '=', 'p.id')
            ->groupBy('cart_id')
            ->selectRaw('cart_id,IFNULL(SUM(products.price*cart_products.quantity), 0) as cart_price');
    }

}
