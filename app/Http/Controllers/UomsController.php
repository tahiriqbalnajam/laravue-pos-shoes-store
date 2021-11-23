<?php

namespace App\Http\Controllers;

use App\Uom;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
class UomsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $units = Uom::where('name', 'like', '%'.$request->keyword.'%')
        ->select('id','name')->get();
        return response()->json(new JsonResponse(['units' => $units]));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //print_r($request->name); die();
        try {
            $validateDate = $this->validate($request,[
                'name'=>'required',
            ]);
            $unit = new Uom();
            $unit->name = $request->name;
            $unit->save();
            return response()->json($unit);
        } catch (\Throwable $th) {
            return response()->json( $th->getMessage(), 403);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Uom  $uom
     * @return \Illuminate\Http\Response
     */
    public function show(Uom $uom)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Uom  $uom
     * @return \Illuminate\Http\Response
     */
    public function edit(Uom $uom)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Uom  $uom
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Uom $uom)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Uom  $uom
     * @return \Illuminate\Http\Response
     */
    public function destroy(Uom $uom)
    {
        //
    }
}
