<?php

namespace App\Http\Controllers;

use App\Accounts;
use DB;
use App\Sale;
use Carbon\Carbon;
use App\SaleProducts;
use App\PurchaseProducts;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use App\Inventories as Inventory;
use App\ProductExpiries as Batch;
use Illuminate\Support\Facades\Auth;
use App\AccountTransactions as Transaction;

class SalesController extends Controller
{
    const ITEM_PER_PAGE = 10;

    public function index(Request $request)
    {
        $isAdmin = (Auth::user()->hasRole('admin')) ? true : false;
        $searchParams = $request->all();
        $customer_id = $request->customer;
        $date = $request->get('daterange');
        $date_from = Carbon::parse($date[0])->startOfDay();
        $date_to = Carbon::parse($date[1])->endOfDay();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
       
        $purchases = Sale::with([
            'customer',
            'products' => function($query) {
                $query->with('product');
                $query->with('product.uom');
            }
        ])
        ->when($date[0], function($query) use ($date) {
            $date_from = Carbon::parse($date[0])->startOfDay();
            $date_to = Carbon::parse($date[1])->endOfDay();
            return $query->whereBetween('created_at', [$date_from, $date_to])->whereDate('created_at', '<=', $date_to);
        })
        ->select('id','total','discount','totalpiad','quantity','total_items', 'customer_id','created_at','type')
        ->when($customer_id, function($query) use ($customer_id) {
            return $query->where('customer_id', '=', $customer_id);
          })
        ->when(!$isAdmin, function($query){
            return $query->where('entry_by', session('user_id'));
        })
        ->paginate( $limit);

        $total_sale = DB::table('sales')
                    ->where('type','=','sale')
                    ->whereBetween('created_at', [$date_from, $date_to])
                    ->when($customer_id, function($query) use ($customer_id) {
                        return $query->where('customer_id', '=', $customer_id);
                      })
                    ->when(!$isAdmin, function($query){
                        return $query->where('entry_by', session('user_id'));
                    })
                    ->get( array(DB::raw('SUM(totalpiad) as total_price'))); 
        $total_sale_return = DB::table('sales')
                    ->where('type','=','return')
                    ->whereBetween('created_at', [$date_from, $date_to])
                    ->when($customer_id, function($query) use ($customer_id) {
                        return $query->where('customer_id', '=', $customer_id);
                      })
                    ->when(!$isAdmin, function($query){
                        return $query->where('entry_by', session('user_id'));
                    })
                    ->get( array(DB::raw('SUM(totalpiad) as total_price')));
                    //echo "SELECT Round(sum((sp.price*sp.quantity - (sp.price*sp.quantity * sp.discount1/100)) - sp.purchase_price*sp.quantity),2) as total_sale_profit FROM `products` p RIGHT JOIN sale_products sp ON p.id = sp.product_id JOIN sales ON sales.id = sp.sale_id WHERE DATE(sp.created_at) >= '$date_from' AND DATE(sp.created_at) <= '$date_to' and sales.type='sale' GROUP BY p.id, DATE(sp.created_at)";


                    
        $total_sale_profit = DB::SELECT("SELECT Round( sum( (sp.price*sp.quantity - ( if( sp.discount_type = 'rs', (sp.discount1+sp.bill_discount), (sp.price*sp.quantity * ((sp.discount1+sp.bill_discount)/100))) ) - sp.purchase_price*sp.quantity)),2) as total_sale_profit FROM `products` p RIGHT JOIN sale_products sp ON p.id = sp.product_id JOIN sales ON sales.id = sp.sale_id WHERE DATE(sp.created_at) >= '$date_from' AND DATE(sp.created_at) <= '$date_to' and sales.type='sale' GROUP BY p.id, DATE(sp.created_at)");
        $total_return_profit = DB::SELECT("SELECT Round(sum((sp.price*sp.quantity - (sp.price*sp.quantity * sp.discount1/100)) - sp.purchase_price*sp.quantity),2) as total_return_profit FROM `products` p RIGHT JOIN sale_products sp ON p.id = sp.product_id JOIN sales ON sales.id = sp.sale_id WHERE DATE(sp.created_at) >= '$date_from' AND DATE(sp.created_at) <= '$date_to' and sales.type='return' GROUP BY p.id, DATE(sp.created_at)");
        return response()->json(new JsonResponse(['sales' => $purchases,'total_sale'=>$total_sale,'total_sale_return'=>$total_sale_return,'total_sale_profit'=>$total_sale_profit,'total_return_profit'=>$total_return_profit]));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getinvoiceid(Request $request)
    {
        $id =  Sale::select('id')->latest()->first();
        return response()->json($id);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      //  echo "<pre>";
       // print_r($request->all());
       $per_product_discount = 0;
        DB::beginTransaction();
        try {
            $validateDate = $this->validate($request,[
                'ttlAmount'=>'required|min:1',
                'ttlItems'=>'required|min:1',
                'ttlQuantity'=>'required|min:1',
            ]);
            $sale = new Sale();
            $sale->type = $request->saletype;
            $sale->total = $request->ttlAmount;
            $sale->discount = $request->discount ?? 0;
            $sale->totalpiad = $request->paid_amount ??  $request->ttlAmount;
            $sale->quantity = $request->ttlQuantity ??  $request->ttlQuantity;
            $sale->total_items = $request->ttlItems ??  $request->ttlItems;
            $sale->customer_id = ($request->customer) ?? null;
            $sale->previous_balance = $request->prev_balance;
            $sale->bill_discount = $request->bill_discount ?? 0;
            $sale->discount_type = $request->discount_type;
            $sale->entry_by = session('user_id');
            $products = $request->products;
            $ptotal = sizeof($products); 
            if($request->bill_discount)
                $per_product_discount = round($request->bill_discount/$ptotal , 2); 
            
            $inventArr = array();
            $profit = 0;
            $pdiscount = 0;
            $inventory_total = 0;
            foreach($products as $product) {
                $profit += ($product['price'] - $product['purchase_price'])*$product['quantity'];
                $pdiscount += $this->get_discount($product['price'],$product['quantity'], $product['discount1'], $product['discount2'], $request->discount_type);
                $inventory_total += ($product['purchase_price']*$product['quantity']);
                $productsArr[] = new SaleProducts( [
                    'product_id' => $product['selectedproduct'],
                    'batch_number' => $product['batch_number'] ?? null,
                    'quantity' => $product['quantity'],
                    'purchase_price' => $product['purchase_price'],
                    'price' => $product['price'],
                    'bonus' => $product['bonus'] ?? 0,
                    'discount1' => $product['discount1'] ?? 0,
                    'discount2' => $product['discount2'] ?? 0,
                    'bill_discount' => $per_product_discount,
                    'discount_type' => $request->discount_type, 
                    ]); 
                $inventArr[] =  array(
                    'outlet_id' => session('outlet_id'),
                    'product_id' => $product['selectedproduct'],
                    'quantity' => $product['quantity'] + $product['bonus'],
                    'inventory_type' => ($request->saletype == 'sale') ? 'sale' : 'sale_return',
                    'batch_id' =>  $product['batch_number']
                ); 
                //echo "<pre>"; print_r($productsArr);
                //die('asdf');
                //$inventory = new Inventory();
                //$inventory->outlet_id = session('outlet_id');
                //$inventory->product_id = $product['selectedproduct'];
                //$inventory->quantity = $product['quantity'] + $product['bonus'];
                //$inventory->inventory_type = ($request->saletype == 'sale') ? 'sale' : 'sale_return';
                //$inventory->batch_id = $product['batch_number'];
                //$inventory->save();
                
            }
            Inventory::insert( $inventArr); // enter inventory recored
            $sale->save(); //save sale3
            $latest_saleid = $sale->id;
            $sale->products()->saveMany($productsArr); //save sale products
            $this->sale_transaction($request, $latest_saleid,$inventory_total, $profit, $pdiscount);
            DB::commit();
            return response()->json(new JsonResponse(['sale' => $sale]));
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json( $th->getMessage(), 403);
        }
        
    }

    public function get_discount($total, $quantity, $disc1, $disc2 = '', $type = '%') {
        if($type == 'prcnt') {
            $discount = ($total*$quantity* ($disc1/100));
            if($disc2)
                $discount = ($discount*$quantity* ($disc2/100));
        } else {
            $discount = $disc1;
            if($disc2)
                $discount = ($discount - $disc2);
        }

        return $discount;
    }

    public function sale_transaction($request, $id, $inventory_total, $profit, $pdiscount) {
        
        $shop_acc = env("IDL_SHOPACC_ID", "1");
        $runcust_acc = env("IDL_RUNCUST_ID", "2");
        $invent_acc = env("IDL_INVENTACC_ID", "3");
        $profit_acc = env("IDL_PROFITACC_ID", "4");
        $trans_cc = new Transaction();
        $trans_dc = new Transaction();
        //first naam customer as we have to get money from customer
        //

        $trans_cc->amount = $request->ttlAmount;
        $trans_dc->amount = $request->paid_amount;
        $trans_cc->entry_by = $trans_dc->entry_by = session('user_id');
        $trans_cc->sale_id = $trans_dc->sale_id = $id;

        ////////////////////////////
        $paid_amount = $request->paid_amount;
        $profit_total = $profit - $pdiscount - $request->bill_discount; //from profit reduce product discount and bill discount
        $saleman_profit = 0;
        $staff = ($request->staff) ? Accounts::find($request->staff) :  null;
        if($staff) {
            $saleman_profit = round($request->paid_amount * ($staff->saleman_profit/100));
            $profit_total -= $saleman_profit;
        }
        ////////////////////////////

        if($request->saletype == 'sale') {
            $transactions = array(
                array(  //inventory+ cust- without profit
                    'amount' =>  $inventory_total, 
                    'naam_account' => ($request->customer) ??  $runcust_acc,
                    'jama_account' => $invent_acc,
                    'comments' => 'sale #'.$id,
                    'entry_by' =>  session('user_id'),
                    'type' => 'custnaam',
                    'sale_id' => $id,
                ),
                array(  //profit+ cust-
                    'amount' =>  $profit_total, 
                    'naam_account' => ($request->customer) ??  $runcust_acc,
                    'jama_account' => $profit_acc,
                    'comments' => 'sale #'.$id,
                    'entry_by' =>  session('user_id'),
                    'type' => 'custnaam',
                    'sale_id' => $id,
                ),
                array(  //cust+ cash-
                    'amount' =>  $request->paid_amount, 
                    'naam_account' => $shop_acc,
                    'jama_account' => ($request->customer) ??  $runcust_acc,
                    'comments' => 'sale #'.$id,
                    'entry_by' =>  session('user_id'),
                    'type' => 'custjama',
                    'sale_id' => $id,
                ),
            );
            if($staff) {
                $transactions[] = array(  //inventory+ cust- without profit
                    'amount' =>  $saleman_profit, 
                    'naam_account' => $shop_acc,
                    'jama_account' => $staff->id,
                    'comments' => 'sale #'.$id,
                    'entry_by' =>  session('user_id'),
                    'type' => 'custnaam',
                    'sale_id' => $id,
                );
            }
            
        } else {
            $transactions = array(
                array(  //inventory+ cust- without profit
                    'amount' =>  $inventory_total, 
                    'naam_account' => $invent_acc,
                    'jama_account' => ($request->customer) ??  $runcust_acc,
                    'comments' => 'sale i+c #'.$id,
                    'entry_by' =>  session('user_id'),
                    'type' => 'custjama',
                    'sale_id' => $id,
                ),
                array(  //profit+ cust-
                    'amount' =>  $profit - $pdiscount - $request->bill_discount, 
                    'naam_account' => $profit_acc,
                    'jama_account' =>  ($request->customer) ??  $runcust_acc,
                    'comments' => 'sale p+c #'.$id,
                    'entry_by' =>  session('user_id'),
                    'type' => 'custjama',
                    'sale_id' => $id,
                ),
                array(  //cust+ cash-
                    'amount' =>  $request->paid_amount, 
                    'naam_account' => ($request->customer) ??  $runcust_acc,
                    'jama_account' => $shop_acc,
                    'comments' => 'sale c+c #'.$id,
                    'entry_by' =>  session('user_id'),
                    'type' => 'custnaam',
                    'sale_id' => $id,
                ),
            );
        } 

        Transaction::insert($transactions);
    }

    public function getbatchs($id) {
        $batches =  Batch::where('product_id', $id)->get();
        return response()->json(new JsonResponse(['batches' => $batches]));
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
       $salen = Sale::where('id',$id)->with(['products','customer'])->first();
        return response()->json(new JsonResponse(['sale' => $salen]));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function edit(Sale $sale)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Sale $sale)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function destroy(Sale $sale)
    {
        //
    }
}
