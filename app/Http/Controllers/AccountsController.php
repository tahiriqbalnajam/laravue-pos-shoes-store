<?php

namespace App\Http\Controllers;

use App\Accounts;
use App\AccountAreas;
use App\AccountTypes;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Arr;
use Carbon\Carbon;
class AccountsController extends Controller
{

    const ITEM_PER_PAGE = 20;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $account_id = $request->account_type;
        $searchParams = $request->all();
        $keyword = ($request->get('keyword')) ?? '';
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $customers = Accounts::with(array('account_type'=>function($query){
                        $query->select('id','title');
                     }))
                     ->when($keyword, function($query) use ($keyword) {
                        $query->where('name', 'like', '%' .$keyword. '%');
                     })
                     ->when($account_id, function($query) use ($account_id) {
                        $query->where('account_type_id','=',$account_id);
                     })
                     ->paginate($limit);

        return response()->json(new JsonResponse(['accounts' => $customers]));
    }

    public function getSaleman() {
        $saleman = Accounts::where('account_type_id', '3')->get();
        return response()->json(new JsonResponse(['saleman' => $saleman]));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       
        $customer = new Accounts([
            'name' => $request->get('name'),
            'phone' => $request->get('phone') ?? '' ,
            'saleman_profit' => $request->get('saleman_profit') ?? '0' , 
            'area_id' => $request->get('area_id') ?? '',
            'address' => $request->get('address') ?? '',
            'account_type_id'=> $request->get('account_type') ?? '1' ,
        ]);
        $customer->save();
        return response()->json(new JsonResponse(['accounts' => [$customer]]));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $account  = Accounts::where('id', $id)->with('account_type','area')->first();
        return response()->json(new JsonResponse(['account' => $account])); 
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function get_areas(Accounts $account)
    {
        $areas = AccountAreas::all();
        return response()->json(new JsonResponse(['areas' => $areas]));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Accounts $account)
    {
       // dd($request->all());
        $customer = Accounts::find($request->id);
        $customer->name = $request->name;
        $customer->phone = $request->phone;
        $customer->saleman_profit = $request->saleman_profit ?? '0';
        $customer->address = $request->address;
        $customer->account_type_id = $request->account_type;
        $customer->save();

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Accounts $account,$id)
    {
        $data = Accounts::findOrFail($id);
        $data->delete();
        return response()->json(['status'=>'Data Deleted']);
    }

    public function search(Request $request, Accounts $account){
        $query = $request->get('query');
        if($query){
            $data = Accounts::where('name','LIKE',"%{$query}%")
                              ->orWhere('address','LIKE',"%{$query}%") 
                              ->orWhere('phone','LIKE',"%{$query}%")   
                              ->get();
                           //   dd($data);
            return response()->json(new JsonResponse(['data' => $data]));                  
        
        }
    }
    public function get_accounts(Request $request, Accounts $account){
            $accounts = AccountTypes::all();
            return response()->json(new JsonResponse(['accounts' => $accounts]));                  
        
    }
}
