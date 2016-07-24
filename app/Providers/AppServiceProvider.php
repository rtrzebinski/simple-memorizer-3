<?php

namespace App\Providers;

use App\Models\Exercise\ExerciseRepository;
use App\Models\Exercise\ExerciseRepositoryInterface;
use App\Models\User\UserRepository;
use App\Models\User\UserRepositoryInterface;
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
        $this->app->bind(ExerciseRepositoryInterface::class, function () {
            return new ExerciseRepository;
        });
        $this->app->bind(UserRepositoryInterface::class, function () {
            return new UserRepository();
        });
    }
}
