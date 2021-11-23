<?php

namespace App\Http\Controllers;

use Exception;
use App\Settings;
use Illuminate\Http\Request;
use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\Artisan;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $settings = Settings::first();
        return response()->json(new JsonResponse(['settings' => $settings]));

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
        try {
            $set = Settings::find(1);
            $set->update($request->all());
            return response()->json(new JsonResponse(['settings' => $set]));
        } catch(Exception $e) {
            return response()->json(new JsonResponse(['error' => $e->getMessage()])); 
        }

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

    function backupdb($database = null){
        Artisan::call("backup:run");
        return response()->json(new JsonResponse(['backup' => '']));
    }
}
