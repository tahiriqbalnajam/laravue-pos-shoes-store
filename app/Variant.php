<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\VariantValue;

class Variant extends Model
{
    public function variant_values()
    {
        return $this->hasMany('App\VariantValue');

    }
}
