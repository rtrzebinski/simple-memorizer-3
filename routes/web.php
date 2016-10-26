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

    Route::get('/lessons/create', 'Web\LessonController@create');

    Route::post('/lessons', 'Web\LessonController@store');

    Route::get('/lessons/{lesson}/learn', 'Web\LessonController@learn');

    Route::get('/lessons/{lesson}/edit', 'Web\LessonController@edit');

    Route::put('/lessons/{lesson}', 'Web\LessonController@update');

    Route::delete('/lessons/{lesson}', 'Web\LessonController@delete');

    Route::post('/lessons/{lesson}/subscribe', 'Web\LessonController@subscribe');

    Route::post('/lessons/{lesson}/unsubscribe', 'Web\LessonController@unsubscribe');

    Route::get('/lessons/{lesson}', 'Web\LessonController@view');

    Route::get('/lessons/{lesson}/exercises/create', 'Web\ExerciseController@create');

    Route::get('/exercises/{exercise}/edit', 'Web\ExerciseController@edit');
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
