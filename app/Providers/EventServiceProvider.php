<?php

namespace App\Providers;

use App\Events\BadAnswer;
use App\Events\GoodAnswer;
use App\Listeners\UpdateNumberOfBadAnswersOfExercise;
use App\Listeners\UpdateNumberOfGoodAnswersOfExercise;
use App\Listeners\UpdatePercentOfGoodAnswersOfExercise;
use App\Listeners\UpdatePercentOfGoodAnswersOfLesson;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        GoodAnswer::class => [
            UpdateNumberOfGoodAnswersOfExercise::class,
            UpdatePercentOfGoodAnswersOfExercise::class,
            UpdatePercentOfGoodAnswersOfLesson::class,
        ],
        BadAnswer::class => [
            UpdateNumberOfBadAnswersOfExercise::class,
            UpdatePercentOfGoodAnswersOfExercise::class,
            UpdatePercentOfGoodAnswersOfLesson::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
