<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Variants;

class VariationSet extends Model
{
    public function variants()
    {
        return $this->hasMany('App\Variant');

    }
}
