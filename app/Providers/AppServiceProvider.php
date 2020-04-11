<?php

namespace App\Providers;

use App\Structures\AuthenticatedUserExerciseRepository;
use App\Structures\AuthenticatedUserExerciseRepositoryInterface;
use App\Structures\AuthenticatedUserLessonRepository;
use App\Structures\AuthenticatedUserLessonRepositoryInterface;
use App\Structures\GuestUserLessonRepository;
use App\Structures\GuestUserLessonRepositoryInterface;
use App\Structures\GuestUserExerciseRepository;
use App\Structures\UserExerciseRepositoryInterface;
use App\Structures\UserLessonRepository;
use App\Structures\UserLessonRepositoryInterface;
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

        $this->app->bind(UserExerciseRepositoryInterface::class, function () {
            if (Auth::check()) {
                return new AuthenticatedUserExerciseRepository(Auth::user());
            }
            return new GuestUserExerciseRepository();
        });

        $this->app->bind(AuthenticatedUserExerciseRepositoryInterface::class, function () {
            return new AuthenticatedUserExerciseRepository(Auth::user());
        });

        $this->app->bind(UserLessonRepositoryInterface::class, function () {
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
