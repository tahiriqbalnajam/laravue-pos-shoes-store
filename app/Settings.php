<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $fillable = [
        'id', 'company_name', 'address','phone','invoice_footer','show_disc2','show_bonus','show_expiry', 'print_size'
    ];
}
