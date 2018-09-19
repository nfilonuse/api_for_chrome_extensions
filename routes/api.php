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

Route::group(['prefix' => 'app/v1'], function () {
	Route::post('/login', 'API\Auth\APILoginController@auth')->name('api_login');
	Route::post('/login_social', 'API\Auth\APILoginController@auth_social')->name('api_login_social');

    Route::post('/register', 'API\Auth\APIRegisterController@register')->name('api_register');

    Route::post('/password/email', 'API\Auth\APIForgotPasswordController@getResetToken');
    Route::post('/password/reset', 'API\Auth\APIResetPasswordController@reset');

    Route::get('/list/countries', 'API\application\ListsController@getCountries');
    Route::get('/list/states/{country_id}', 'API\application\ListsController@getStates');

    Route::resource('subscriptions', 'API\application\SubscriptionController');

    Route::group(['middleware' => 'jwt.auth'], function () {
        Route::resource('companys', 'API\application\CompanyController');
        Route::resource('companys', 'API\application\CompanyController');
        Route::resource('roles', 'API\application\RoleController');
        Route::resource('citations', 'API\application\CitationController');
        Route::group(['prefix' => 'user'], function () {
            Route::resource('subscriptions', 'API\application\UserSubscriptionsController');
        });
        Route::get('/subscription/checkout/{id}','API\application\UserSubscriptionsController@checkout');
        Route::get('/session','API\application\AccountController@session');
        Route::post('/account/update','API\application\AccountController@update');
        Route::post('/account/updatePassword','API\application\AccountController@updatePassword');
        Route::post('/agreement','API\application\AccountController@setagreement_flag');
        Route::post('/payment/stripe','API\application\SubscriptionController@payment');
        
        //Route::get('/subscriptions/checkout/{subscription_id}/{token}', 'Classes\Users\Subscription@checkout')->name('user.subscription.checkout');
        //Route::post('/subscriptions/checkout/', 'Classes\Users\Subscription@submit')->name('user.subscription.checkout.post');
                
        Route::get('/logout', 'API\Auth\APILoginController@logout')->name('api_logout');
    });
});
