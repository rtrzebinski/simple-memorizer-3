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
use App\Models\ExerciseResult\ExerciseResult;
use App\Models\Lesson\Lesson;
use App\Models\User\User;

$factory->define(User::class, function () {
    return [
        'email' => uniqid() . '@example.com',
        'password' => uniqid(),
        'api_token' => uniqid(),
    ];
});

$factory->define(Exercise::class, function (Faker\Generator $faker) {
    return [
        'lesson_id' => function () {
            return factory(Lesson::class)->create()->id;
        },
        'question' => $faker->words(8, true),
        'answer' => $faker->words(2, true),
    ];
});

$factory->define(Lesson::class, function (Faker\Generator $faker) {
    return [
        'owner_id' => function () {
            return factory(User::class)->create()->id;
        },
        'name' => $faker->words(10, true),
        'visibility' => 'public',
    ];
});

$factory->define(ExerciseResult::class, function () {
    return [
        'user_id' => factory(User::class)->create()->id,
        'exercise_id' => factory(Exercise::class)->create()->id,
    ];
});
