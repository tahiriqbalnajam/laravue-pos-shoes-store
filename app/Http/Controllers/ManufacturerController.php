<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Manufacturer;
use App\Laravue\JsonResponse;

class ManufacturerController extends Controller
{

    public function index(Request $request)
    {
        $manfuacturer = Manufacturer::where('name', 'like', '%'.$request->keyword.'%')
        ->select('id','name')->get();
        return response()->json(new JsonResponse(['manfuacturers' => $manfuacturer]));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validateDate = $this->validate($request,[
                'name'=>'required|unique:manufacturers,name',
            ]);
            $manufac = new Manufacturer();
            $manufac->name = $request->name;
            $manufac->save();
            return response()->json($manufac);
        } catch (\Throwable $th) {
            return response()->json( $th->getMessage(), 403);
        }
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
        $manufact = Manufacturer::find($request->id);
        $manufact->name = $request->name;
        $manufact->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $manufact = Manufacturer::findOrFail($id);
        $manufact->delete();
        return response()->json(['status'=>'Manufacture Deleted']);
    }
    public function search_manufact(Request $request){
        $query = $request->get('query');
        if($query){
            $result = Manufacturer::where('name','LIKE',"%{$query}%")  
                              ->get();
                           //   dd($data);
            return response()->json(new JsonResponse(['result' => $result]));                  
        
        }
    }
}
