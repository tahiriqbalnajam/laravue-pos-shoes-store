<?php

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Laravue\Faker;
use \App\Laravue\JsonResponse;
use \App\Laravue\Acl;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::namespace('Api')->group(function() {
    Route::post('auth/login', 'AuthController@login');
    Route::group(['middleware' => 'auth:sanctum'], function () {
        // Auth routes
        Route::get('auth/user', 'AuthController@user');
        Route::post('auth/logout', 'AuthController@logout');

        Route::get('/user', function (Request $request) {
            return new UserResource($request->user());
        });
        //IDLBridge Routes
        // Api resource routes
        Route::apiResource('roles', 'RoleController')->middleware('permission:' . Acl::PERMISSION_PERMISSION_MANAGE);
        Route::apiResource('users', 'UserController')->middleware('permission:' . Acl::PERMISSION_USER_MANAGE);
        Route::apiResource('permissions', 'PermissionController')->middleware('permission:' . Acl::PERMISSION_PERMISSION_MANAGE);

        // Custom routes
        Route::put('users/{user}', 'UserController@update');
        Route::get('users/{user}/permissions', 'UserController@permissions')->middleware('permission:' . Acl::PERMISSION_PERMISSION_MANAGE);
        Route::put('users/{user}/permissions', 'UserController@updatePermissions')->middleware('permission:' .Acl::PERMISSION_PERMISSION_MANAGE);
        Route::get('roles/{role}/permissions', 'RoleController@permissions')->middleware('permission:' . Acl::PERMISSION_PERMISSION_MANAGE);
    });
});
Route::get('backupdb', 'SettingController@backupdb');
Route::get('stock_by_manufacturer', 'ProductController@stock_by_manufacturer');
Route::get('stock_value_report', 'ProductController@stock_value_report');
Route::get('stock_retial_value_report', 'ProductController@stock_retial_value_report');
Route::get('get_stock_print', 'ProductController@get_stock_print');
Route::resource('customer','AccountsController');
Route::get('get_saleman','AccountsController@getSaleman');
Route::get('get_accounts', 'AccountsController@get_accounts');
Route::resource('product', 'ProductController');
Route::resource('variants', 'VariationsController');
Route::resource('manufacturer', 'ManufacturerController');
Route::get('search_manufact', 'ManufacturerController@search_manufact');
Route::resource('unitsmeasure', 'UomsController');
Route::get('featured_product', 'ProductController@featured');
Route::get('grandreport', 'SalesController@grandreport');
Route::get('lastinvoiceid', 'SalesController@getinvoiceid');
Route::get('wheatlastinvoiceid/{id}', 'WheatController@getwheatinvoiceid');
Route::get('lastpurinvoiceid', 'SalesController@getinvoiceid');
Route::get('dashboardtop', 'DashboardController@dashboardtop');
Route::get('daily_sale_linechart', 'DashboardController@daily_sale_line_chart');
Route::get('total_accounts', 'DashboardController@total_accounts');
Route::resource('sale', 'SalesController');
Route::post('exchange_products', 'SalesController@exchange_products');
Route::resource('settings', 'SettingController');
//Route::get('getbatchs/{id}', 'SalesController@getbages');
Route::get('getbatchs/{id}', 'SalesController@getbatchs');
Route::get('getpreviousbalance/{id}/{ajax}', 'AccountTransactionsController@acc_total');
Route::resource('purchase', 'PurchasesController');
Route::get('returntype/{id}', 'PurchasesController@data_return_type');
Route::post('purchase_req', 'PurchasesController@store');
Route::resource('transaction', 'AccountTransactionsController');
Route::get('udhar_total', 'AccountTransactionsController@udhar_total');
Route::resource('category', 'ProductCategoryController');
Route::get('get_stock/{id}', 'ProductController@getstock');
Route::get('stock_data_dashboard/', 'ProductController@stock_data_dashboard');
Route::get('get_product_stock/{id}', 'ProductController@get_product_stock');
Route::post('import_products', 'ProductController@import_products');
Route::post('add_stock', 'ProductController@addstock');
Route::post('edit_price', 'ProductController@edit_price');
Route::resource('areas', 'AccountAreasController');
Route::get('get_khata_details','AccountTransactionsController@get_khata_details');
Route::get('get_khata_details_date','AccountTransactionsController@get_khata_details_date');
Route::get('/search_outlet','OutletController@search');
Route::resource('outlet','OutletController');
Route::resource('wheat','WheatController');
// Fake APIs
Route::get('/table/list', function () {
    $rowsNumber = mt_rand(20, 30);
    $data = [];
    for ($rowIndex = 0; $rowIndex < $rowsNumber; $rowIndex++) {
        $row = [
            'author' => Faker::randomString(mt_rand(5, 10)),
            'display_time' => Faker::randomDateTime()->format('Y-m-d H:i:s'),
            'id' => mt_rand(100000, 100000000),
            'pageviews' => mt_rand(100, 10000),
            'status' => Faker::randomInArray(['deleted', 'published', 'draft']),
            'title' => Faker::randomString(mt_rand(20, 50)),
        ];

        $data[] = $row;
    }

    return response()->json(new JsonResponse(['items' => $data]));
});

Route::get('/orders', function () {
    $rowsNumber = 8;
    $data = [];
    for ($rowIndex = 0; $rowIndex < $rowsNumber; $rowIndex++) {
        $row = [
            'order_no' => 'LARAVUE' . mt_rand(1000000, 9999999),
            'price' => mt_rand(10000, 999999),
            'status' => Faker::randomInArray(['success', 'pending']),
        ];

        $data[] = $row;
    }

    return response()->json(new JsonResponse(['items' => $data]));
});

Route::get('/articles', function () {
    $rowsNumber = 10;
    $data = [];
    for ($rowIndex = 0; $rowIndex < $rowsNumber; $rowIndex++) {
        $row = [
            'id' => mt_rand(100, 10000),
            'display_time' => Faker::randomDateTime()->format('Y-m-d H:i:s'),
            'title' => Faker::randomString(mt_rand(20, 50)),
            'author' => Faker::randomString(mt_rand(5, 10)),
            'comment_disabled' => Faker::randomBoolean(),
            'content' => Faker::randomString(mt_rand(100, 300)),
            'content_short' => Faker::randomString(mt_rand(30, 50)),
            'status' => Faker::randomInArray(['deleted', 'published', 'draft']),
            'forecast' => mt_rand(100, 9999) / 100,
            'image_uri' => 'https://via.placeholder.com/400x300',
            'importance' => mt_rand(1, 3),
            'pageviews' => mt_rand(10000, 999999),
            'reviewer' => Faker::randomString(mt_rand(5, 10)),
            'timestamp' => Faker::randomDateTime()->getTimestamp(),
            'type' => Faker::randomInArray(['US', 'VI', 'JA']),

        ];

        $data[] = $row;
    }

    return response()->json(new JsonResponse(['items' => $data, 'total' => mt_rand(1000, 10000)]));
});

Route::get('articles/{id}', function ($id) {
    $article = [
        'id' => $id,
        'display_time' => Faker::randomDateTime()->format('Y-m-d H:i:s'),
        'title' => Faker::randomString(mt_rand(20, 50)),
        'author' => Faker::randomString(mt_rand(5, 10)),
        'comment_disabled' => Faker::randomBoolean(),
        'content' => Faker::randomString(mt_rand(100, 300)),
        'content_short' => Faker::randomString(mt_rand(30, 50)),
        'status' => Faker::randomInArray(['deleted', 'published', 'draft']),
        'forecast' => mt_rand(100, 9999) / 100,
        'image_uri' => 'https://via.placeholder.com/400x300',
        'importance' => mt_rand(1, 3),
        'pageviews' => mt_rand(10000, 999999),
        'reviewer' => Faker::randomString(mt_rand(5, 10)),
        'timestamp' => Faker::randomDateTime()->getTimestamp(),
        'type' => Faker::randomInArray(['US', 'VI', 'JA']),

    ];

    return response()->json(new JsonResponse($article));
});

Route::get('articles/{id}/pageviews', function ($id) {
    $pageviews = [
        'PC' => mt_rand(10000, 999999),
        'Mobile' => mt_rand(10000, 999999),
        'iOS' => mt_rand(10000, 999999),
        'android' => mt_rand(10000, 999999),
    ];
    $data = [];
    foreach ($pageviews as $device => $pageview) {
        $data[] = [
            'key' => $device,
            'pv' => $pageview,
        ];
    }

    return response()->json(new JsonResponse(['pvData' => $data]));
});
