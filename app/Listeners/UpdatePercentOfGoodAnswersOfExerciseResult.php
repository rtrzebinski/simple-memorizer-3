<?php

namespace App\Listeners;

use App\Events\ExerciseEvent;
use App\Events\ExerciseResultPercentOfGoodAnswersUpdated;
use App\Models\ExerciseResult;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdatePercentOfGoodAnswersOfExerciseResult implements ShouldQueue
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
        $exerciseId = $event->exerciseId();
        $user = $event->user();

        $exerciseResult = ExerciseResult::whereExerciseId($exerciseId)->whereUserId($user->id)->first();
        $exerciseResult->percent_of_good_answers = $this->calculatePercentOfGoodAnswers($exerciseResult);
        $exerciseResult->save();

        event(new ExerciseResultPercentOfGoodAnswersUpdated($exerciseId, $user));
    }

    /**
     * @param ExerciseResult $exerciseResult
     * @return int
     */
    private function calculatePercentOfGoodAnswers(ExerciseResult $exerciseResult): int
    {
        // some predefined values for first few good answers without any bad answers
        // this is to prevent an exercise from jumping to 100% too quickly,
        // we want exercise to be shown few times even if user knows it already

        if ($exerciseResult->number_of_good_answers == 1 && $exerciseResult->number_of_bad_answers == 0) {
            return 20;
        }

        if ($exerciseResult->number_of_good_answers == 2 && $exerciseResult->number_of_bad_answers == 0) {
            return 40;
        }

        if ($exerciseResult->number_of_good_answers == 3 && $exerciseResult->number_of_bad_answers == 0) {
            return 60;
        }

        if ($exerciseResult->number_of_good_answers == 4 && $exerciseResult->number_of_bad_answers == 0) {
            return 80;
        }

        // for other cases just calculate

        $totalNumberOfAnswers = $exerciseResult->number_of_good_answers + $exerciseResult->number_of_bad_answers;

        if ($totalNumberOfAnswers) {
            return round(100 * $exerciseResult->number_of_good_answers / ($totalNumberOfAnswers));
        } else {
            return 0;
        }
    }
}
