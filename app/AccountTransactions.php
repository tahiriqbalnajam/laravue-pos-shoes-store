<?php

namespace App;
use DB;
use Illuminate\Database\Eloquent\Model;

class AccountTransactions extends Model
{
    protected $fillable = ['jama_account, naam_account, amount, comments, entry_by, status, sale_id, purchase_id'];

    public function naam_account()
    {
        return $this->belongsTo('App\Accounts','naam_account')->select(array('id', 'name'));

    }
    public function jama_account()
    {
        return $this->belongsTo('App\Accounts','jama_account')->select(array('id', 'name'));

    }
    
}
