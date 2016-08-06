<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

use App\Models\Exercise\Exercise;
use App\Models\Lesson\Lesson;
use App\Models\User\User;

$factory->define(User::class, function () {
    return [
        'email' => uniqid() . '@example.com',
        'password' => uniqid(),
        'api_token' => uniqid(),
    ];
});

$factory->define(Exercise::class, function () {
    return [
        'lesson_id' => function () {
            return factory(Lesson::class)->create()->id;
        },
        'question' => uniqid(),
        'answer' => uniqid(),
    ];
});

$factory->define(Lesson::class, function () {
    return [
        'owner_id' => function () {
            return factory(User::class)->create()->id;
        },
        'name' => uniqid(),
        'visibility' => 'public',
    ];
});
