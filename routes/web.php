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

Route::group(['middleware' => ['auth']], function () {

    Route::get('/home', 'Web\HomeController@index');

    Route::post('logout', 'Web\LoginController@logout');

    Route::get('/lessons/learn', function () {
        return view('lessons.learn');
    });

    Route::get('/lessons/view', function () {
        return view('lessons.view');
    });

    Route::get('/lessons/create', function () {
        return view('lessons.create');
    });

    Route::get('/lessons/edit', function () {
        return view('lessons.edit');
    });

    Route::get('/exercises/create', function () {
        return view('exercises.create');
    });

    Route::get('/exercises/edit', function () {
        return view('exercises.edit');
    });
});

Route::group(['middleware' => ['guest']], function () {

    Route::get('/', 'Web\MainController@index');

    Route::get('login/{driver}', 'Web\SocialiteController@redirectToProvider');

    Route::get('login/callback/{driver}', 'Web\SocialiteController@handleProviderCallback');

    Route::get('login', 'Web\LoginController@showLoginForm')->name('login');

    Route::post('login', 'Web\LoginController@login');

    Route::get('register', 'Web\RegisterController@showRegistrationForm');

    Route::post('register', 'Web\RegisterController@register');

    Route::get('password/reset', 'Web\ForgotPasswordController@showLinkRequestForm');

    Route::post('password/email', 'Web\ForgotPasswordController@sendResetLinkEmail');

    Route::get('password/reset/{token}', 'Web\ResetPasswordController@showResetForm');

    Route::post('password/reset', 'Web\ResetPasswordController@reset');

});
