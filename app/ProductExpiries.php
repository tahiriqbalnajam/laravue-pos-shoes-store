<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductExpiries extends Model
{
    protected $fillable = [
        'id', 'product_id', 'batch_no','exp_date'
    ];
}
