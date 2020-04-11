<?php

namespace App\Providers;

use App\Structures\AuthenticatedUserExerciseRepository;
use App\Structures\AuthenticatedUserExerciseRepositoryInterface;
use App\Structures\AuthenticatedUserLessonRepository;
use App\Structures\AuthenticatedUserLessonRepositoryInterface;
use App\Structures\GuestUserLessonRepository;
use App\Structures\GuestUserLessonRepositoryInterface;
use App\Structures\UserExerciseRepository;
use App\Structures\UserExerciseRepositoryInterface;
use App\Structures\UserLessonRepository;
use App\Structures\UserLessonRepositoryInterface;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
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
            return new UserExerciseRepository();
        });

        $this->app->bind(AuthenticatedUserExerciseRepositoryInterface::class, function () {
            return new AuthenticatedUserExerciseRepository();
        });

        $this->app->bind(UserLessonRepositoryInterface::class, function () {
            return new UserLessonRepository();
        });

        $this->app->bind(AuthenticatedUserLessonRepositoryInterface::class, function () {
            return new AuthenticatedUserLessonRepository();
        });

        $this->app->bind(GuestUserLessonRepositoryInterface::class, function () {
            return new GuestUserLessonRepository();
        });
    }
}
