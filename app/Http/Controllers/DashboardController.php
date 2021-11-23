<?php

namespace App\Http\Controllers;

use App\Accounts;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use App\Purchase;
use App\Sale;
use App\Product;
use DB;
class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function dashboardtop() {
        $sales = Sale::select('id')->where('created_at','like', date('Y-m-d').'%')->where('type', 'sale')->get();
        $totalsales = $sales->count();

        $products = Product::select('id');
        $totalproducts = $products->count();

        $accounts = Accounts::select('id')->where('status', 'enable')->get();
        $totalaccounts = $accounts->count();
        return response()->json(new JsonResponse(['totalsales' => $totalsales, 'totalproducts' => $totalproducts, 'totalaccounts' => $totalaccounts]));
    }

    public function daily_sale_line_chart() {
        $sales = Sale::selectRaw('date(created_at) as date,sum(total) as total')->groupby(DB::raw('date(created_at)'))->limit(7)->orderBy('created_at', 'desc')->get();
        return response()->json(new JsonResponse(['sales' => $sales]));
    }

    public function total_accounts() {
        $accounts = Accounts::select('id')->where('status', 'enable')->get();
        $totalaccounts = $accounts->count();
        return response()->json(new JsonResponse(['accounts' => $totalaccounts]));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
}
