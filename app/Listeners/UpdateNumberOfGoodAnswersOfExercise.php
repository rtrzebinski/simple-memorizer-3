<?php

namespace App\Listeners;

use App\Events\GoodAnswer;
use App\Models\ExerciseResult;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class UpdateNumberOfGoodAnswersOfExercise implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * @param GoodAnswer $event
     * @return void
     */
    public function handle(GoodAnswer $event)
    {
        $exercise = $event->exercise;
        $userId = $event->userId;

        $exerciseResult = ExerciseResult::whereExerciseId($exercise->id)->whereUserId($userId)->first();

        if (is_null($exerciseResult)) {
            // create new exercise result
            $exerciseResult = new ExerciseResult();
            $exerciseResult->user_id = $userId;
            $exerciseResult->exercise_id = $exercise->id;
            $exerciseResult->number_of_good_answers = 1;
            $exerciseResult->save();
        } else {
            // increase number of answers for existing exercise result
            DB::table('exercise_results')->where('id', '=', $exerciseResult->id)
                ->update(['number_of_good_answers' => DB::raw('number_of_good_answers + 1')]);
        }
    }
}
