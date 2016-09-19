<?php

/*
 |--------------------------------------------------------------------------
 | Application Routes
 |--------------------------------------------------------------------------
 |
 | Here is where you can register all of the routes for an application.
 | It's a breeze. Simply tell Laravel the URIs it should respond to
 | and give it the controller to call when that URI is requested.
 |
 */

Route::post('/api/signup', 'Api\UserController@signup');

Route::post('/api/login', 'Api\UserController@login');

Route::group(['middleware' => ['auth:api', 'throttle:60,1']], function () {

    Route::post('/lessons', 'Api\LessonController@createLesson');

    Route::get('/lessons/owned', 'Api\LessonController@fetchOwnedLessons');

    Route::post('/lessons/{lesson}/user', 'Api\LessonController@subscribeLesson');

    Route::delete('/lessons/{lesson}/user', 'Api\LessonController@unsubscribeLesson');

    Route::get('/lessons/subscribed', 'Api\LessonController@fetchSubscribedLessons');

    Route::get('/lessons/{lesson}', 'Api\LessonController@fetchLesson');

    Route::patch('/lessons/{lesson}', 'Api\LessonController@updateLesson');

    Route::delete('/lessons/{lesson}', 'Api\LessonController@deleteLesson');


    Route::post('/lessons/{lesson}/exercises', 'Api\ExerciseController@createExercise');

    Route::get('/exercises/{exercise}', 'Api\ExerciseController@fetchExercise');

    Route::get('/lessons/{lesson}/exercises', 'Api\ExerciseController@fetchExercisesOfLesson');

    Route::patch('/exercises/{exercise}', 'Api\ExerciseController@updateExercise');

    Route::delete('/exercises/{exercise}', 'Api\ExerciseController@deleteExercise');

    Route::get('/lessons/{lesson}/exercises/random', 'Api\ExerciseController@fetchRandomExerciseOfLesson');

    Route::post('/exercises/{exercise}/increase-number-of-good-answers-of-user',
        'Api\ExerciseController@increaseNumberOfGoodAnswersOfUser');

    Route::post('/exercises/{exercise}/increase-number-of-bad-answers-of-user',
        'Api\ExerciseController@increaseNumberOfBadAnswersOfUser');

});
