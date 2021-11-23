<?php

namespace App\Http\Controllers;

use App\Wheat;
use App\WheatCart;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use DB;
class WheatController extends Controller
{
    const ITEM_PER_PAGE = 10;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $searchParams = $request->all();
        $date = $request->get('daterange') ?? array();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $wheat = Wheat::with('cart_products')
                        ->when($date[0], function($query) use ($date) {
                            $date_from = Carbon::parse($date[0])->startOfDay();
                            $date_to = Carbon::parse($date[1])->endOfDay();
                            return $query->whereBetween('created_at', [$date_from, $date_to]);
                        })
                        ->paginate( $limit);
        $total_wheat_bill = DB::table('wheat')
                ->select(DB::raw('SUM(total_bill) as total_sales'))
                ->when($date[0], function($query) use ($date) {
                            $date_from = Carbon::parse($date[0])->startOfDay();
                            $date_to = Carbon::parse($date[1])->endOfDay();
                            return $query->whereBetween('created_at', [$date_from, $date_to]);
                        })
                ->get(); 
        $total_wheat_weight = DB::table('wheat')
                ->select(DB::raw('SUM(total_weight) as total_weight'))
                ->when($date[0], function($query) use ($date) {
                            $date_from = Carbon::parse($date[0])->startOfDay();
                            $date_to = Carbon::parse($date[1])->endOfDay();
                            return $query->whereBetween('created_at', [$date_from, $date_to]);
                        })
                ->get(); 
        $total_wheat_rate = DB::table('wheat')
                ->select(DB::raw('round(AVG(rate),0) as total_rate'))
                ->when($date[0], function($query) use ($date) {
                            $date_from = Carbon::parse($date[0])->startOfDay();
                            $date_to = Carbon::parse($date[1])->endOfDay();
                            return $query->whereBetween('created_at', [$date_from, $date_to]);
                        })
                ->get();
        $net_weight = DB::table('wheat')
                ->select(DB::raw('SUM(net_weight) as net_weight'))
                ->when($date[0], function($query) use ($date) {
                            $date_from = Carbon::parse($date[0])->startOfDay();
                            $date_to = Carbon::parse($date[1])->endOfDay();
                            return $query->whereBetween('created_at', [$date_from, $date_to]);
                        })
                ->get();             
               // print_r($total_wheat_rate); //die();                 
        return response()->json(new JsonResponse([
            'wheat' => $wheat,
            'total_wheat_bill' => $total_wheat_bill, 
            'total_wheat_weight' => $total_wheat_weight,
            'total_wheat_rate' => $total_wheat_rate,
            'net_weight' => $net_weight
        ]));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request 
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       // echo "<pre>";
       // print_r($request->all());die();
        $customer_name = $request->customer_name;
        $phone = $request->phone;
        $address = $request->address;
        $kaat = $request->kaat;
        $quality_kaat = $request->quality_kaat;
        $total_bill = $request->total_bill;
        $decided_rate = $request->decided_rate;
        $final_total = $request->final_total;
        $cart = $request->cart;
        $wheat = new Wheat();
        $wheat->customer_name = $customer_name;
        $wheat->phone = $phone;
        $wheat->address = $address;
        $wheat->kaat = $kaat;
        $wheat->quality_kaat = $quality_kaat;
        $wheat->total_bill = $total_bill;
        $wheat->rate = $decided_rate;
        $wheat->total_weight = $final_total;
        $wheat->net_weight = $request->net_weight;
        $wheat->save();
        foreach($cart as $item) {
            //print_r($item);
            $cart = new WheatCart();
            $cart->wheat_id = $wheat->id;
            $cart->bags  = $item['bags'];
            $cart->weight = $item['weight'];
            $cart->save();
        }
        $id = $wheat->id;
        return response()->json(['id'=>$id]); 
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        
    }

    public function getwheatinvoiceid($id)
    {
       // echo $id;die();
        $data =  Wheat::where('id',$id)->with('cart_products')->first();
       // return response()->json(new JsonResponse(['data' => $data]));
        return response()->json(['data'=>$data]);
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
        $data = Wheat::findorFail($id);
        $card_data = WheatCart::where('wheat_id',$id)->delete();
        $data->delete();
        return response()->json(['status'=>'Deleted']);

    }
}
