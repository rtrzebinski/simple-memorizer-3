<?php

namespace App\Providers;

use App\Models\Exercise;
use App\Models\Lesson;
use App\Policies\ExercisePolicy;
use App\Policies\LessonPolicy;
use App\Policies\UserExercisePolicy;
use App\Policies\UserLessonPolicy;
use App\Structures\UserExercise;
use App\Structures\UserLesson;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Exercise::class => ExercisePolicy::class,
        Lesson::class => LessonPolicy::class,
        UserExercise::class => UserExercisePolicy::class,
        UserLesson::class => UserLessonPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
