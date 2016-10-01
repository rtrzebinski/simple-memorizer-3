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

// Authentication Routes...
$this->get('login', 'Web\LoginController@showLoginForm')->name('login');

$this->post('login', 'Web\LoginController@login');

$this->post('logout', 'Web\LoginController@logout');

// Registration Routes...
$this->get('register', 'Web\RegisterController@showRegistrationForm');

$this->post('register', 'Web\RegisterController@register');

// Password Reset Routes...
$this->get('password/reset', 'Web\ForgotPasswordController@showLinkRequestForm');

$this->post('password/email', 'Web\ForgotPasswordController@sendResetLinkEmail');

$this->get('password/reset/{token}', 'Web\ResetPasswordController@showResetForm');

$this->post('password/reset', 'Web\ResetPasswordController@reset');

Route::get('/', 'Web\MainController@index');

Route::get('/home', 'Web\HomeController@index');
