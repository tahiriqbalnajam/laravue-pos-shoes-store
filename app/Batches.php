<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Batches extends Model
{
    protected $table = 'product_expiries';
    public $timestamps = false;
    protected $fillable = ['product_id','batch_no','exp_date'];

}
