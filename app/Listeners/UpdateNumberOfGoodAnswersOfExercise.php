<?php

namespace App\Listeners;

use App\Events\ExerciseGoodAnswer;
use App\Models\ExerciseResult;
use Carbon\Carbon;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

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
            // create new exercise result
            $exerciseResult = new ExerciseResult();
            $exerciseResult->user_id = $user->id;
            $exerciseResult->exercise_id = $exercise->id;
            $exerciseResult->number_of_good_answers = 1;
            $exerciseResult->latest_good_answer = Carbon::now();
            $exerciseResult->save();
        } else {
            // increase number of answers for existing exercise result
            DB::table('exercise_results')->where('id', '=', $exerciseResult->id)
                ->update([
                    'number_of_good_answers' => DB::raw('number_of_good_answers + 1'),
                    'latest_good_answer' => Carbon::now(),
                ]);
        }
    }
}
