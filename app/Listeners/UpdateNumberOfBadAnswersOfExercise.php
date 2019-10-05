<?php

namespace App\Listeners;

use App\Events\ExerciseBadAnswer;
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
     * @param ExerciseBadAnswer $event
     * @return void
     */
    public function handle(ExerciseBadAnswer $event)
    {
        $exercise = $event->exercise();
        $user = $event->user();

        $exerciseResult = ExerciseResult::whereExerciseId($exercise->id)->whereUserId($user->id)->first();

        if (is_null($exerciseResult)) {
            // create new exercise result
            $exerciseResult = new ExerciseResult();
            $exerciseResult->user_id = $user->id;
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
