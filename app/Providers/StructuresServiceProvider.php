<?php

namespace App\Providers;

use App\Structures\UserExercise\AuthenticatedUserExerciseRepository;
use App\Structures\UserExercise\AuthenticatedUserExerciseRepositoryInterface;
use App\Structures\UserExercise\GuestUserExerciseRepository;
use App\Structures\UserExercise\UserExerciseRepositoryInterface;
use App\Structures\UserLesson\AuthenticatedUserLessonRepository;
use App\Structures\UserLesson\AuthenticatedUserLessonRepositoryInterface;
use App\Structures\UserLesson\GuestUserLessonRepository;
use App\Structures\UserLesson\GuestUserLessonRepositoryInterface;
use App\Structures\UserLesson\UserLessonRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class StructuresServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // UserExercise operations valid for all (authenticated and guest) users
        $this->app->bind(UserExerciseRepositoryInterface::class, function () {
            if (Auth::check()) {
                return new AuthenticatedUserExerciseRepository(Auth::user());
            }
            return new GuestUserExerciseRepository();
        });

        // UserExercise operations valid for authenticated users only
        $this->app->bind(AuthenticatedUserExerciseRepositoryInterface::class, function () {
            return new AuthenticatedUserExerciseRepository(Auth::user());
        });

        // UserLesson operations valid for all (authenticated and guest) users
        $this->app->bind(UserLessonRepositoryInterface::class, function () {
            if (Auth::check()) {
                return new AuthenticatedUserLessonRepository(Auth::user());
            }
            return new GuestUserLessonRepository();
        });

        // UserLesson operations valid for authenticated users only
        $this->app->bind(AuthenticatedUserLessonRepositoryInterface::class, function () {
            return new AuthenticatedUserLessonRepository(Auth::user());
        });

        // UserLesson operations valid for guest users only
        $this->app->bind(GuestUserLessonRepositoryInterface::class, function () {
            return new GuestUserLessonRepository();
        });
    }
}
