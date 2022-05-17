<?php


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
Route::get('/cacheClear', function() {
    $optimize = Artisan::call('optimize:clear');
    $configclear = Artisan::call('config:clear');
    return 'Routes cache cleared';
  });

Route::get('/login', 'App\Http\Controllers\Api\Auth\LoginController@login')->name('login');
  //Authentication Routes
Route::post('social/loginFacebook', 'App\Http\Controllers\Api\Auth\LoginController@loginFacebook');
Route::post('social/loginGoogle', 'App\Http\Controllers\Api\Auth\LoginController@loginGoogle');
Route::get('/avatars/image/{filename}', 'App\Http\Controllers\Api\Auth\LoginController@getImg');
Route::post('/register', 'App\Http\Controllers\Api\Auth\RegisterController@register');
Route::post('/register/verify', 'App\Http\Controllers\Api\Auth\RegisterController@registerVerify');
Route::post('/login', 'App\Http\Controllers\Api\Auth\LoginController@login');
Route::post('/phone/check', 'App\Http\Controllers\Api\Auth\ResetPasswordController@checkresetUsingPhone');
Route::post('/phone/reset', 'App\Http\Controllers\Api\Auth\ResetPasswordController@resetUsingPhone');
Route::post('/disconnect', 'App\Http\Controllers\Api\Auth\LoginController@logout');
Route::post('/refresh', 'App\Http\Controllers\Api\Auth\LoginController@refresh');
Route::get('/paypal', 'App\Http\Controllers\Api\Auth\LoginController@paypal');
  //Routes that require an authenticated user

  Route::middleware(['auth:api', 'doNotCacheResponse'])->group(function () {


    Route::post('/email/resend', 'App\Http\Controllers\Api\Auth\EmailVerificationController@resend')->name('verification.resend');
  
    Route::post('/logout', 'App\Http\Controllers\Api\Auth\LoginController@logout');
    Route::post('/update', 'App\Http\Controllers\Api\Auth\LoginController@update');
    Route::post('/updatePaypal', 'App\Http\Controllers\Api\Auth\LoginController@updatePaypal');
    Route::post('/addPlanToUser', 'App\Http\Controllers\Api\Auth\LoginController@addPlanToUser');
    Route::post('/addPlanToUsermtn', 'App\Http\Controllers\Api\Auth\LoginController@addPlanToUserOther');
    Route::post('/addPlanToUsercard', 'App\Http\Controllers\Api\Auth\LoginController@addPlanToUserOtherCard');
    Route::post('/addPlanToUserairtel', 'App\Http\Controllers\Api\Auth\LoginController@addPlanToUserOtherAirtel');
    Route::get('/cancelSubscription', 'App\Http\Controllers\Api\Auth\LoginController@cancelSubscription');
    Route::get('/cancelSubscriptionPaypal', 'App\Http\Controllers\Api\Auth\LoginController@cancelSubscriptionPaypal');
    Route::get('/profile', 'App\Http\Controllers\Api\Auth\LoginController@profile');
    Route::get('/user', 'App\Http\Controllers\Api\Auth\LoginController@user');
    Route::get('/user/wallet', 'App\Http\Controllers\Api\Auth\LoginController@userWallet');
    Route::get('/avatar/{avatarid}', 'UserController@show');
    Route::put('/account/update', 'UserController@update');
    Route::get('/user/devices', 'UserController@devices');
    Route::post('/device/logout', 'UserController@device_logout');
    Route::post('/device/check', 'UserController@device_check');
    Route::post('/device/validate', 'App\Http\Controllers\Api\Auth\LoginController@checkUserDevice');
    Route::get('/account/isSubscribed', 'UserController@isSubscribed');
    Route::post('/user/avatar', 'App\Http\Controllers\Api\Auth\LoginController@update_avatar');
  
    Route::post('/setRazorPay', 'App\Http\Controllers\Api\Auth\LoginController@setRazorPay');
  
  
    Route::post('/users/addprofile', 'App\Http\Controllers\Api\Auth\LoginController@createNewProfile');
  
    
    });
  


