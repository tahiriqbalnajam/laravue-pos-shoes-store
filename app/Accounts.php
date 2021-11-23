<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Accounts extends Model
{
    public $timestamps = false;
    protected $fillable = ['name','area_id','address','phone','account_type_id','status'];

    public function account_type()
    {
        return $this->belongsTo('App\AccountTypes', 'account_type_id');
    }

    public function area()
    {
        return $this->belongsTo('App\AccountAreas');
    }

}
