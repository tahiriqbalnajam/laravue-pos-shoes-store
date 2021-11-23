<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Arr;
use DB;
use App\AccountTransactions as ATrans;
use Carbon\Carbon;

class AccountTransactionsController extends Controller
{
    const ITEM_PER_PAGE = 10;
    public function index(Request $request)
    {
        $isAdmin = (Auth::user()->hasRole('admin')) ? true : false;
        $searchParams = $request->all();
        $date = $request->get('daterange');
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $transactions = ATrans::with([
            'jama_account','naam_account'
        ])
        ->when($date[0], function($query) use ($date) {
            $date_from = Carbon::parse($date[0])->startOfDay();
            $date_to = Carbon::parse($date[1])->endOfDay();
            return $query->whereBetween('created_at', [$date_from, $date_to]);
        })
        ->when(!$isAdmin, function($query){
            return $query->where('entry_by', session('user_id'));
        })
        ->select('id','jama_account','naam_account','amount', 'comments', 'status', 'created_at')
        ->orderby('created_at', 'desc')
        ->paginate( $limit);
        return response()->json(new JsonResponse(['transactions' => $transactions]));
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'jama_account' => 'required|numeric',
            'naam_account' => 'required|numeric',
            'amount' => 'required|numeric',
        ]);
        
        $transaction = new ATrans();
        $transaction->jama_account = $request->get('jama_account');
        $transaction->naam_account = $request->get('naam_account');
        $transaction->amount = $request->get('amount');
        $transaction->comments = $request->get('comments');
        $transaction->entry_by = session('user_id');
        $transaction->save();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
       $trans =  ATrans::find($id);
       $trans->status = ($trans->status == 'disable') ? 'enable' : 'disable';
       $trans->save();
    }

    public function get_khata_details(Request $request) {
        $date = $request->get('daterange');
        $searchParams = $request->all();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $id = (int)$request->id;
        $acc_total = $this->acc_total($id);
        $transactions = ATrans::when($date, function($q) use($date) {
                                $datefrom = Carbon::parse($date[0])->startOfDay();
                                $dateto = Carbon::parse($date[1])->endOfDay();
                                return $q->whereBetween('date',array($datefrom,$dateto));
                            })
                            ->where(function($query) use ($id) {
                                $query->where("jama_account", $id)
                                        ->orWhere("naam_account", $id);
                            })
                            ->where('status','enable')
                            ->selectRaw('sum(amount) as amount, jama_account, naam_account,comments, entry_by, created_at, updated_at')
                            ->orderby('created_at', 'desc')
                            ->orderby('sale_id', 'desc')
                            ->groupBy('naam_account','sale_id','purchase_id')
                            //->toSql($limit); 
                            ->paginate($limit); 
                            //echo $transactions;
                            //dd();
        $trans_collection_last_date = $transactions->getCollection()->first()->created_at;
        $total_last_date_on_page = $this->acc_total_from_date($id, $trans_collection_last_date);
        $transactions->getCollection()->transform(function ($transaction) use ($total_last_date_on_page, &$balance, $id) {
            if(!isset($balance))
                $balance = $total_last_date_on_page[0]->acc_total;
            
            $transaction['balance'] =  $balance;
            if($transaction->jama_account == $id){
                $balance -= $transaction->amount;
            } else {
                $balance += $transaction->amount;
            }           
            return $transaction;
        });

        return response()->json(new JsonResponse(['data' => $transactions, 'acc_total' => $acc_total, 'last_date_total' => $total_last_date_on_page]));
    }
    public function get_khata_details_date(Request $request) {
        $date = $request->get('daterange');
        $date_from = Carbon::parse($date[0])->startOfDay();
        $date_to = Carbon::parse($date[1])->endOfDay();
        $request->id = 1;
        $id = $request->id;
        $searchParams = $request->all();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $transactions = ATrans::whereDate('created_at','>=',$date_from)
                                ->whereDate('created_at','<=',$date_to)
                                ->where(function($query) use ($id) {
                                    $query->where("jama_account", $id)
                                          ->orWhere("naam_account", $id);
                                        })
                                ->orderby('created_at', 'desc')
                                ->where('status','enable')
                                ->paginate($limit); 
        return response()->json(new JsonResponse(['data' => $transactions]));
    }

    public function acc_total_from_date($id, $fromdate) { 
        $balance =  DB::select("SELECT IFNULL(sum(IFNULL(totaljama,0)-IFNULL(totalnaam,0)),0) as acc_total from 
          (SELECT sum(amount) as totaljama from account_transactions where jama_account = $id AND status='enable' AND created_at <= '$fromdate') as jama 
          JOIN 
          (SELECT sum(amount) as totalnaam from account_transactions where naam_account = $id AND status='enable'  AND created_at <= '$fromdate') as naam");
         return  $balance;
      }

    public function acc_total($id, $ajax = false) { 
       $balance =  DB::select("SELECT IFNULL(sum(IFNULL(totaljama,0)-IFNULL(totalnaam,0)),0) as acc_total from 
         (SELECT sum(amount) as totaljama from account_transactions where jama_account = $id AND status='enable') as jama 
         JOIN 
         (SELECT sum(amount) as totalnaam from account_transactions where naam_account = $id AND status='enable') as naam");

        if($ajax)
            return response()->json(new JsonResponse(['prev_balnace' => $balance]));
        return  $balance;
     }
     public function udhar_total() { 
        $udhar =  DB::select("SELECT name, phone,address,
                (
                    IFNULL((select sum(amount) from account_transactions act where status = 'enable' AND act.jama_account = a.id),0) 
                    - 
                    IFNULL((select sum(amount) from account_transactions act where status = 'enable' AND act.naam_account = a.id),0)
                ) as total from accounts a WHERE a.id NOT IN(1,2) GROUP by a.id ORDER by total ASC");
        return response()->json(new JsonResponse(['udhar' => $udhar]));
      }
}
