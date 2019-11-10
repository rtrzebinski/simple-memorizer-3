<?php

namespace App\Providers;

use App\Events\ExerciseResultUpdated;
use App\Events\ExerciseBadAnswer;
use App\Events\ExerciseCreated;
use App\Events\ExerciseDeleted;
use App\Events\ExerciseGoodAnswer;
use App\Events\ExerciseResultPercentOfGoodAnswersUpdated;
use App\Events\LessonAggregatesUpdated;
use App\Listeners\UpdateNumberOfBadAnswersOfExercise;
use App\Listeners\UpdateNumberOfGoodAnswersOfExercise;
use App\Listeners\UpdatePercentOfGoodAnswersOfExerciseResult;
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
        ExerciseGoodAnswer::class => [
            UpdateNumberOfGoodAnswersOfExercise::class,
        ],
        ExerciseBadAnswer::class => [
            UpdateNumberOfBadAnswersOfExercise::class,
        ],
        ExerciseResultUpdated::class => [
            UpdatePercentOfGoodAnswersOfExerciseResult::class,
        ],
        ExerciseResultPercentOfGoodAnswersUpdated::class => [
            UpdatePercentOfGoodAnswersOfLesson::class,
        ],
        ExerciseCreated::class => [
            UpdatePercentOfGoodAnswersOfLesson::class,
        ],
        ExerciseDeleted::class => [
            UpdatePercentOfGoodAnswersOfLesson::class,
        ],
        LessonAggregatesUpdated::class => [
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
