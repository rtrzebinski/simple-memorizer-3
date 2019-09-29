<?php

namespace App\Listeners;

use App\Events\BadAnswer;
use App\Models\ExerciseResult;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class UpdateNumberOfBadAnswersOfExercise implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * @param BadAnswer $event
     * @return void
     */
    public function handle(BadAnswer $event)
    {
        $exercise = $event->exercise;
        $userId = $event->userId;

        $exerciseResult = ExerciseResult::whereExerciseId($exercise->id)->whereUserId($userId)->first();

        if (is_null($exerciseResult)) {
            // create new exercise result
            $exerciseResult = new ExerciseResult();
            $exerciseResult->user_id = $userId;
            $exerciseResult->exercise_id = $exercise->id;
            $exerciseResult->number_of_bad_answers = 1;
            $exerciseResult->save();
        } else {
            // increase number of answers for existing exercise result
            DB::table('exercise_results')->where('id', '=', $exerciseResult->id)
                ->update(['number_of_bad_answers' => DB::raw('number_of_bad_answers + 1')]);
        }
    }
}
