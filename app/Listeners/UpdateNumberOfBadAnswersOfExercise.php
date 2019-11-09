<?php

namespace App\Listeners;

use App\Events\ExerciseAnswerUpdated;
use App\Events\ExerciseBadAnswer;
use App\Models\ExerciseResult;
use Carbon\Carbon;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateNumberOfBadAnswersOfExercise
{
    /**
     * Handle the event.
     *
     * @param ExerciseBadAnswer $event
     * @return void
     */
    public function handle(ExerciseBadAnswer $event)
    {
        $exerciseId = $event->exerciseId();
        $user = $event->user();

        $exerciseResult = ExerciseResult::whereExerciseId($exerciseId)->whereUserId($user->id)->first();

        if (is_null($exerciseResult)) {
            // create new exercise result if user never answered before
            $exerciseResult = new ExerciseResult();
            $exerciseResult->user_id = $user->id;
            $exerciseResult->exercise_id = $exerciseId;
            $exerciseResult->number_of_bad_answers = 1;
            $exerciseResult->number_of_bad_answers_today = 1;
            $exerciseResult->latest_bad_answer = Carbon::now();
            $exerciseResult->save();
        } else {
            if ($exerciseResult->latest_bad_answer instanceof Carbon && $exerciseResult->latest_bad_answer->isToday()) {
                // increment number_of_bad_answers_today if there was bad answer today already
                $exerciseResult->increment('number_of_bad_answers_today');
            } else {
                // set number_of_bad_answers_today to 1 if this is first bad answer today
                $exerciseResult->number_of_bad_answers_today = 1;
                $exerciseResult->save();
            }

            // increment number_of_bad_answers
            $exerciseResult->increment('number_of_bad_answers');

            // set latest_bad_answer to today
            $exerciseResult->latest_bad_answer = Carbon::now();
            $exerciseResult->save();
        }

        event(new ExerciseAnswerUpdated($exerciseId, $user));
    }
}
