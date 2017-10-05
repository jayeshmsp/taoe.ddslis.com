<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix'=>'v0'], function () {
    Route::post('login', '\App\Http\Controllers\API\V1\UserController@login');
    /*Route::get('details', '\App\Http\Controllers\API\V1\UserController@details')->middleware('auth:api');*/
    
    Route::post('user', '\App\Http\Controllers\API\V1\UserController@create');
    Route::patch('user', '\App\Http\Controllers\API\V1\UserController@update');
    Route::delete('user', '\App\Http\Controllers\API\V1\UserController@delete');

    Route::get('user/list', '\App\Http\Controllers\API\V1\UserController@getList');
});
