<?php

namespace App\Providers;

use App\Events\ExerciseResultUpdated;
use App\Events\ExerciseBadAnswer;
use App\Events\ExerciseCreated;
use App\Events\ExerciseDeleted;
use App\Events\ExerciseGoodAnswer;
use App\Events\ExerciseResultPercentOfGoodAnswersUpdated;
use App\Events\ExercisesMerged;
use App\Events\LessonAggregatesUpdated;
use App\Events\LessonSubscribed;
use App\Events\LessonUnsubscribed;
use App\Listeners\UpdateChildLessonsCountOfLesson;
use App\Listeners\UpdateExercisesCountOfLesson;
use App\Listeners\UpdateNumberOfBadAnswersOfExercise;
use App\Listeners\UpdateNumberOfGoodAnswersOfExercise;
use App\Listeners\UpdatePercentOfGoodAnswersOfExerciseResult;
use App\Listeners\UpdatePercentOfGoodAnswersOfLesson;
use App\Listeners\UpdateSubscribersCountOfLesson;
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
            UpdateExercisesCountOfLesson::class,
        ],
        ExercisesMerged::class => [
            UpdatePercentOfGoodAnswersOfLesson::class,
            UpdateExercisesCountOfLesson::class,
        ],
        ExerciseDeleted::class => [
            UpdatePercentOfGoodAnswersOfLesson::class,
            UpdateExercisesCountOfLesson::class,
        ],
        LessonAggregatesUpdated::class => [
            UpdatePercentOfGoodAnswersOfLesson::class,
            UpdateExercisesCountOfLesson::class,
            UpdateChildLessonsCountOfLesson::class,
        ],
        LessonSubscribed::class => [
            UpdateSubscribersCountOfLesson::class,
        ],
        LessonUnsubscribed::class => [
            UpdateSubscribersCountOfLesson::class,
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
