<?php

namespace App\Providers;

use App\Structures\UserExercise\AuthenticatedUserExerciseRepository;
use App\Structures\UserExercise\AuthenticatedUserExerciseRepositoryInterface;
use App\Structures\UserLesson\AuthenticatedUserLessonRepository;
use App\Structures\UserLesson\AuthenticatedUserLessonRepositoryInterface;
use App\Structures\UserLesson\GuestUserLessonRepository;
use App\Structures\UserLesson\GuestUserLessonRepositoryInterface;
use App\Structures\UserExercise\GuestUserExerciseRepository;
use App\Structures\UserExercise\AbstractUserExerciseRepositoryInterface;
use App\Structures\UserLesson\AbstractUserLessonRepositoryInterface;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
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
        if ($this->app->environment() !== 'production') {
            $this->app->register(IdeHelperServiceProvider::class);
        }

        // force HTTPS on production
        if ($this->app->environment() === 'production') {
            URL::forceScheme('https');
        }

        $this->app->bind(AbstractUserExerciseRepositoryInterface::class, function () {
            if (Auth::check()) {
                return new AuthenticatedUserExerciseRepository(Auth::user());
            }
            return new GuestUserExerciseRepository();
        });

        $this->app->bind(AuthenticatedUserExerciseRepositoryInterface::class, function () {
            return new AuthenticatedUserExerciseRepository(Auth::user());
        });

        $this->app->bind(AbstractUserLessonRepositoryInterface::class, function () {
            if (Auth::check()) {
                return new AuthenticatedUserLessonRepository(Auth::user());
            }
            return new GuestUserLessonRepository();
        });

        $this->app->bind(AuthenticatedUserLessonRepositoryInterface::class, function () {
            return new AuthenticatedUserLessonRepository(Auth::user());
        });

        $this->app->bind(GuestUserLessonRepositoryInterface::class, function () {
            return new GuestUserLessonRepository();
        });
    }
}
