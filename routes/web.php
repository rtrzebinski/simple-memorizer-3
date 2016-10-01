<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::group(['middleware' => ['guest']], function () {

    Route::get('/', 'Web\MainController@index');

    Route::get('login', 'Web\LoginController@showLoginForm')->name('login');

    Route::post('login', 'Web\LoginController@login');

    Route::get('register', 'Web\RegisterController@showRegistrationForm');

    Route::post('register', 'Web\RegisterController@register');

    Route::get('password/reset', 'Web\ForgotPasswordController@showLinkRequestForm');

    Route::post('password/email', 'Web\ForgotPasswordController@sendResetLinkEmail');

    Route::get('password/reset/{token}', 'Web\ResetPasswordController@showResetForm');

    Route::post('password/reset', 'Web\ResetPasswordController@reset');

});

Route::group(['middleware' => ['auth']], function () {

    Route::get('/home', 'Web\HomeController@index');

    Route::post('logout', 'Web\LoginController@logout');
});
