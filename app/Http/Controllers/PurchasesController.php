<?php

namespace App\Http\Controllers;
use DB;
use Validator;
use App\Batches;
use App\Product;
use App\Purchase;
use Carbon\Carbon;
use App\PurchaseProducts;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use App\Inventories as Inventory;
use App\ProductExpiries as Baatch;
use App\AccountTransactions as ATrans;
use App\AccountTransactions as Transaction;

class PurchasesController extends Controller
{

    const ITEM_PER_PAGE = 10;

    public function index(Request $request)
    {
        $total_purchase = 0;
        $searchParams = $request->all();
        $supplier = $request->supplier;
        $date = $request->get('daterange');
        $date_from = Carbon::parse($date[0])->startOfDay();
        $date_to = Carbon::parse($date[1])->endOfDay();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $purchases = Purchase::with([
            'supplier',
            'products' => function($query) {
                $query->with('product');
            }
        ])
        ->when($date[0], function($query) use ($date) {
            $date_from = Carbon::parse($date[0])->startOfDay();
            $date_to = Carbon::parse($date[1])->endOfDay();
            return $query->whereBetween('created_at', [$date_from, $date_to])->whereDate('created_at', '<=', $date_to);
        })
        ->select('*')
        ->when($supplier, function($query) use ($supplier) {
            return $query->where('supplier_id', '=', $supplier);
          })
        ->paginate( $limit);
        $total_purchase = DB::table('purchases')
                        ->whereBetween('created_at', [$date_from, $date_to])
                        ->where('purchase_type','=','purchase')
                        ->when($supplier, function($query) use ($supplier) {
                            return $query->where('supplier_id', '=', $supplier);
                          })
                        ->sum('subtotal');
        //echo $total_purchase; 
        return response()->json(new JsonResponse(['purchases' => $purchases, 'totalpurchase' => $total_purchase]));
    }

    public function store(Request $request)
    {
       // echo "<pre>";
       // print_r($request->all());die();
        DB::beginTransaction();
        try {
            $messages = array(
                'supplier.required' => 'Please enter supplier.'
            );
            $validator = $this->validate($request,[
                'supplier'=>'required',
                'ttlQuantity'=>'required|min:1',
                'ttlQuantity'=>'required|min:1',
            ], $messages );
            $purchase_type = '';
            $purchase = new Purchase();
            $purchase->supplier_id = $request->supplier;
            $purchase->purchase_type = $request->purchasetype;
            $purchase->bill_discount = $request->bill_discount;
            $purchase->discount_type = $request->discount_type;
            $purchase->quantity = $request->ttlQuantity;
            $purchase->total_items = $request->ttlItems;
            $purchase->total_amount = $request->ttlAmount;
            $purchase->paid_amount = $request->paid_amount;
            if($request->created_at)
                $purchase->created_at = $request->created_at;
            $purchase->entry_by = session('user_id');
            $products = $request->products;
            $ptotal = sizeof($products);
            $per_product_discount = ($request->bill_discount) ? round($request->bill_discount/$ptotal , 2) : 0;
            
            //print_r($products);die();
            foreach($products as $product) {
                $productsArr[] = new PurchaseProducts( [
                    'product_id' => $product['id'],
                    'batch_number' => $product['batch_number'] ?? null,
                    'quantity' => $product['quantity'],
                    'purchase_price' => $product['price'],
                    'price' => $product['price'],
                    'bonus' => $product['bonus'] ?? 0,
                    'discount1' => $product['discount1'] ?? 0,
                    'discount2' => $product['discount2'] ?? 0,
                    'bill_discount' => $per_product_discount,
                    'discount_type' => $request->discount_type, 
                    ]);
                //update product price
                $pro_price = Product::find($product['id']);
                $pro_price->purchase_price =  $this->avg_price($product);
                $pro_price->save();

                $inventArr[] =  array(
                    'outlet_id' => session('outlet_id'),
                    'product_id' => $product['id'],
                    'quantity' => $product['quantity'] + $product['bonus'],
                    'inventory_type' => ($request->purchasetype == 'purchase') ? 'purchase' : 'purchase_return',
                    'batch_id' =>  $product['batch_no']
                );
            }
            Inventory::insert( $inventArr);
            $purchase->save();
            $purchase_id = $purchase->id;
            $purchase->products()->saveMany($productsArr);
            $this->purchase_transaction($request, $purchase_id);
            DB::commit();
            return response()->json(new JsonResponse(['purchz' => $purchase]));
            
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json( $th->getMessage(), 403);
        }
    }

    public function avg_price($product){
        $current_price = $product['purchase_price'];
        $current_stock = $product['totalStock'];

        $new_price = $product['price'];
        $new_stock = $product['quantity'];

        $current_value = $current_price*$current_stock;
        $new_value = $new_price*$new_stock;

        $total_value = $current_value+$new_value;
        $total_stock = $current_stock+$new_stock;

        return round($total_value/$total_stock , 2);
    }

    public function purchase_transaction($request, $id) {
        $shop_acc = env("IDL_SHOPACC_ID", "1");
        $runcust_acc = env("IDL_RUNCUST_ID", "2");
        $invent_acc = env("IDL_INVENTACC_ID", "3");

        if($request->purchasetype == 'purchase') {
            
            $transactions = array(
                array(  //inventory+ suplier- without profit
                    'amount' =>  $request->ttlAmount, 
                    'naam_account' => $invent_acc,
                    'jama_account' => ($request->supplier) ??  $runcust_acc,
                    'comments' => 'purchase s+i- #'.$id,
                    'entry_by' =>  session('user_id'),
                    'sale_id' => $id,
                ),
                array(  //cash+ sup-
                    'amount' =>  $request->paid_amount, 
                    'naam_account' => ($request->supplier) ??  $runcust_acc,
                    'jama_account' => $shop_acc,
                    'comments' => 'purchase c+ s- #'.$id,
                    'entry_by' =>  session('user_id'),
                    'sale_id' => $id,
                ),
            );
            
        } else {
            $transactions = array(
                array(  //inventory+ suplier- without profit
                    'amount' =>  $request->ttlAmount, 
                    'naam_account' => ($request->supplier) ??  $runcust_acc,
                    'jama_account' => $invent_acc,
                    'comments' => 'purchase return i+s- #'.$id,
                    'entry_by' =>  session('user_id'),
                    'sale_id' => $id,
                ),
                array(  //cash+ sup-
                    'amount' =>  $request->paid_amount, 
                    'naam_account' =>  $shop_acc,
                    'jama_account' => ($request->supplier) ??  $runcust_acc,
                    'comments' => 'purchase c+ s- #'.$id,
                    'entry_by' =>  session('user_id'),
                    'sale_id' => $id,
                ),
            );
        }
        Transaction::insert($transactions);
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
        //
    }
    
    public function data_return_type(Request $request, $id){
        $purchases = PurchaseProducts::with(['batches','product'])
                                    ->where('purchase_id','=',$id)
                                    ->get();
        return response()->json(new JsonResponse(['purchases' => $purchases]));
    }
    public function getinvoiceid(Request $request)
    {
        $id =  Purchase::select('id')->latest()->first();
        return response()->json($id);
    }
}