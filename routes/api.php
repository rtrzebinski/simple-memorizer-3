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
| All api routes has '/api/' url prefix defined in RouteServiceProvider.
|
*/

Route::post('/register', 'Api\UserRegisterApiController@register');

Route::post('/login', 'Api\UserLoginApiController@login');

Route::post('/password/email', 'Api\ForgotPasswordApiController@sendResetLinkEmail');

Route::group(
    ['middleware' => ['auth:api']],
    function () {
        Route::post('/lessons', 'Api\LessonApiController@storeLesson');

        Route::get('/lessons/owned', 'Api\LessonApiController@fetchOwnedLessons');

        Route::post('/lessons/{lesson}/user', 'Api\LessonApiController@subscribeLesson');

        Route::delete('/lessons/{lesson}/user', 'Api\LessonApiController@unsubscribeLesson');

        Route::get('/lessons/subscribed', 'Api\LessonApiController@fetchSubscribedLessons');

        Route::get('/lessons/{lesson}', 'Api\LessonApiController@fetchLesson');

        Route::patch('/lessons/{lesson}', 'Api\LessonApiController@updateLesson');

        Route::delete('/lessons/{lesson}', 'Api\LessonApiController@deleteLesson');

        Route::post('/lessons/{lesson}/exercises', 'Api\ExerciseApiController@storeExercise');

        Route::get('/exercises/{exercise}', 'Api\ExerciseApiController@fetchExercise');

        Route::get('/lessons/{lesson}/exercises', 'Api\ExerciseApiController@fetchExercisesOfLesson');

        Route::patch('/exercises/{exercise}', 'Api\ExerciseApiController@updateExercise');

        Route::delete('/exercises/{exercise}', 'Api\ExerciseApiController@deleteExercise');

        Route::get('/lessons/{lesson}/exercises/random', 'Api\LearnLessonApiController@fetchRandomExerciseOfLesson');

        Route::post('/exercises/{exercise}/handle-good-answer', 'Api\LearnLessonApiController@handleGoodAnswer');

        Route::post('/exercises/{exercise}/handle-bad-answer', 'Api\LearnLessonApiController@handleBadAnswer');
    }
);
