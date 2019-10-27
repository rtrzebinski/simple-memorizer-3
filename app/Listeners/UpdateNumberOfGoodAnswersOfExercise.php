<?php

namespace App\Listeners;

use App\Events\ExerciseGoodAnswer;
use App\Models\ExerciseResult;
use Carbon\Carbon;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateNumberOfGoodAnswersOfExercise implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * @param ExerciseGoodAnswer $event
     * @return void
     */
    public function handle(ExerciseGoodAnswer $event)
    {
        $exercise = $event->exercise();
        $user = $event->user();

        $exerciseResult = ExerciseResult::whereExerciseId($exercise->id)->whereUserId($user->id)->first();

        if (is_null($exerciseResult)) {
            // create new exercise result if user never answered before
            $exerciseResult = new ExerciseResult();
            $exerciseResult->user_id = $user->id;
            $exerciseResult->exercise_id = $exercise->id;
            $exerciseResult->number_of_good_answers = 1;
            $exerciseResult->number_of_good_answers_today = 1;
            $exerciseResult->latest_good_answer = Carbon::now();
            $exerciseResult->save();
        } else {
            if ($exerciseResult->latest_good_answer instanceof Carbon && $exerciseResult->latest_good_answer->isToday()) {
                // increment number_of_good_answers_today if there was good answer today already
                $exerciseResult->increment('number_of_good_answers_today');
            } else {
                // set number_of_good_answers_today to 1 if this is first good answer today
                $exerciseResult->number_of_good_answers_today = 1;
                $exerciseResult->save();
            }

            // increment number_of_good_answers
            $exerciseResult->increment('number_of_good_answers');

            // set latest_good_answer to today
            $exerciseResult->latest_good_answer = Carbon::now();
            $exerciseResult->save();
        }
    }
}
