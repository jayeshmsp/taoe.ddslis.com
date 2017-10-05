<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*Route::get('/', function () {
    return view('auth.login');
});*/

Auth::routes();
Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');
Route::get('register/verify/{token}', 'Auth\RegisterController@verify'); 
Route::post('register/verify/{id}', 'Auth\RegisterController@verifyStore'); 

Route::get('/home', 'HomeController@index')->name('home');

/*SOCIALITE AUTHENTICATION ROUTE SECTION*/
Route::get('auth/{provider}', 'Auth\LoginController@redirectToProvider');
Route::get('auth/{provider}/callback', 'Auth\LoginController@handleProviderCallback');


/*PINTEREST AUTHENTICATION ROUTE SECTION*/
Route::get('custom-auth/{provider}', 'Auth\LoginController@redirectToCustomProvider');
Route::get('custom-auth/{provider}/callback', 'Auth\LoginController@handleCustomProviderCallback');

/*PAYMENT*/
Route::get('payment','Auth\LoginController@getPayment');
Route::post('payment','Auth\LoginController@postPayment');

Route::get('manageMailChimp', 'MailChimpController@manageMailChimp');
Route::post('subscribe',['as'=>'subscribe','uses'=>'MailChimpController@subscribe']);
Route::post('sendCompaign',['as'=>'sendCompaign','uses'=>'MailChimpController@sendCompaign']);

/*USER MANAGEMENT*/
Route::group(['middleware' => ['role:admin|user']], function()
{
	/*PROFILE*/
        Route::get('/', 'UserController@index')->name('home');
	Route::get('user/profile','UserController@profile');
	Route::post('user/profile','UserController@postProfile');

	Route::resource('user','UserController');

	Route::post('customer/create-secret-key','CustomerController@createSecretKey');
	Route::resource('customer','CustomerController');

	Route::resource('role','RoleController');
	Route::resource('permission','PermissionController');
	Route::get('role/{id}/permission', 'RoleController@permissions');
	Route::post('role/{id}/permission', 'RoleController@permissionsStore');

	Route::post('chat/upload', 'ChatController@upload');
	Route::resource('chat', 'ChatController');
        Route::get("user/destroy/{id}",'UserController@destroy');
        

});

/*ADMIN ROUTE*/
Route::group(['middleware' => ['role:admin']], function(){
	Route::get('logs','SettingController@logs');
	Route::resource('setting','SettingController');
	Route::post('change-password','UserController@resetPassword');
	
	Route::get('api_log','ApiLogController@index');
	Route::get('logs/view','ApiLogController@view');

	
});
Route::get("send_otp/{id}/{msg}",'\App\Http\Controllers\Auth\RegisterController@send_otp');
Route::get("code_verification/{user_id}",'\App\Http\Controllers\Auth\RegisterController@code_verification');
Route::get("resend-activation-token/{user_id}",'\App\Http\Controllers\Auth\RegisterController@resendActivationToken');
Route::get("resend-activation-email/{user_id}",'\App\Http\Controllers\Auth\RegisterController@resendActivationEmail');

