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

// all users

Route::post('/lessons/{lesson}/subscribe', 'Web\LessonWebController@subscribe');

Route::post('/lessons/{lesson}/subscribe-and-learn', 'Web\LessonWebController@subscribeAndLearn');

// authorised users only

Route::group(
    ['middleware' => ['auth']],
    function () {
        Route::get('/home', 'Web\HomeWebController@index');

        Route::post('logout', 'Web\UserLoginWebController@logout');

        // lessons

        Route::get('/lessons/create', 'Web\LessonWebController@create');

        Route::post('/lessons', 'Web\LessonWebController@store');

        Route::get('/lessons/{lesson}/edit', 'Web\LessonWebController@edit');

        Route::put('/lessons/{lesson}/edit', 'Web\LessonWebController@saveEdit');

        Route::get('/lessons/{lesson}/settings', 'Web\LessonWebController@settings');

        Route::put('/lessons/{lesson}/settings', 'Web\LessonWebController@saveSettings');

        Route::delete('/lessons/{lesson}', 'Web\LessonWebController@delete');

        Route::post('/lessons/{lesson}/unsubscribe', 'Web\LessonWebController@unsubscribe');

        Route::get('/lessons/aggregate/{parentLesson}', 'Web\LessonAggregateWebController@index');

        Route::post('/lessons/aggregate/{parentLesson}', 'Web\LessonAggregateWebController@sync');

        Route::get('/lessons/merge/{lesson}', 'Web\LessonMergeWebController@index');

        Route::post('/lessons/merge/{lesson}', 'Web\LessonMergeWebController@merge');

        Route::post('/lessons/{lesson}/favourite', 'Web\LessonWebController@saveFavourite');

        // lesson CSV export and import

        Route::get('/lessons/{lesson}/csv', 'Web\LessonCsvWebController@exportLessonToCsv');

        Route::post('/lessons/{lesson}/csv', 'Web\LessonCsvWebController@importLessonFromCsv');

        // exercises

        Route::get('/lessons/{lesson}/exercises/create-many', 'Web\ExerciseWebController@createMany');

        Route::post('/lessons/{lesson}/exercises-many', 'Web\ExerciseWebController@storeMany');

        Route::get('/lessons/{lesson}/exercises/create', 'Web\ExerciseWebController@create');

        Route::post('/lessons/{lesson}/exercises', 'Web\ExerciseWebController@store');

        Route::get('/exercises/{exercise}/edit', 'Web\ExerciseWebController@edit');

        Route::put('/exercises/{exercise}', 'Web\ExerciseWebController@update');

        Route::delete('/exercises/{exercise}', 'Web\ExerciseWebController@delete');

        Route::get('/exercises/search', 'Web\ExerciseSearchWebController@searchForExercises');

        // learn all

        Route::get('/learn/all', 'Web\LearnAllWebController@learnAll');

        Route::post('/learn/all', 'Web\LearnAllWebController@handleAnswer');

        // learn favourites

        Route::get('/learn/favourites', 'Web\LearnFavouritesWebController@learnFavourites');

        Route::post('/learn/favourites', 'Web\LearnFavouritesWebController@handleAnswer');

        // learn lesson

        Route::get('/learn/lessons/{lesson_id}', 'Web\LearnLessonWebController@learnLesson');

        Route::post('/learn/lessons/{lesson_id}', 'Web\LearnLessonWebController@handleAnswer');
    }
);

// guest users only

Route::group(
    ['middleware' => ['guest']],
    function () {
        Route::get('/', 'Web\MainWebController@index');

        Route::get('login', 'Web\UserLoginWebController@showLoginForm')->name('login');

        Route::post('login', 'Web\UserLoginWebController@login');

        Route::get('register', 'Web\UserRegisterWebController@showRegistrationForm');

        Route::post('register', 'Web\UserRegisterWebController@register');

        Route::get('password/reset', 'Web\ForgotPasswordWebController@showLinkRequestForm');

        Route::post('password/email', 'Web\ForgotPasswordWebController@sendResetLinkEmail');

        Route::get('password/reset/{token}', 'Web\ResetPasswordWebController@showResetForm');

        Route::post('password/reset', 'Web\ResetPasswordWebController@reset');
    }
);

// lessons

Route::get('/lessons/{lesson}', 'Web\LessonWebController@view');

Route::get('/lessons/{lesson}/exercises', 'Web\LessonWebController@exercises');
