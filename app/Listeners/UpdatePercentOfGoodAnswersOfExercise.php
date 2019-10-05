<?php

namespace App\Listeners;

use App\Events\ExerciseEvent;
use App\Models\ExerciseResult;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdatePercentOfGoodAnswersOfExercise implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * @param ExerciseEvent $event
     * @return void
     */
    public function handle(ExerciseEvent $event)
    {
        $exercise = $event->exercise();
        $user = $event->user();

        $exerciseResult = ExerciseResult::whereExerciseId($exercise->id)->whereUserId($user->id)->first();
        $exerciseResult->percent_of_good_answers = $this->calculatePercentOfGoodAnswers($exerciseResult);
        $exerciseResult->save();
    }

    /**
     * @param ExerciseResult $exerciseResult
     * @return int
     */
    private function calculatePercentOfGoodAnswers(ExerciseResult $exerciseResult): int
    {
        $totalNumberOfAnswers = $exerciseResult->number_of_good_answers + $exerciseResult->number_of_bad_answers;

        if ($totalNumberOfAnswers) {
            return round(100 * $exerciseResult->number_of_good_answers / ($totalNumberOfAnswers));
        } else {
            return 0;
        }
    }
}
