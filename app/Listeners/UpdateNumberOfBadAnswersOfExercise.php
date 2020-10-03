<?php

namespace App\Listeners;

use App\Events\ExerciseResultUpdated;
use App\Events\ExerciseBadAnswer;
use App\Models\ExerciseResult;
use Carbon\Carbon;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

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
            // exercise result already exist, we need to update it,

            $data = [];

            if ($exerciseResult->latest_bad_answer instanceof Carbon && $exerciseResult->latest_bad_answer->isToday()) {
                // increment number_of_bad_answers_today if there was bad answer today already
                $data['number_of_bad_answers_today'] = DB::raw('number_of_bad_answers_today + 1');
            } else {
                // set number_of_bad_answers_today to 1 if this is first bad answer today
                $data['number_of_bad_answers_today'] = '1';
            }

            // increment number_of_bad_answers
            $data['number_of_bad_answers'] = DB::raw('number_of_bad_answers + 1');

            // set latest_bad_answer to now
            $data['latest_bad_answer'] = Carbon::now();

            // set updated_at to now
            $data['updated_at'] = Carbon::now();

            // in order to reduce number of sql queries let's use raw DB update,
            // rather that using Eloquent model which would make this 3 queries
            DB::table('exercise_results')
                ->where('exercise_results.id', '=', $exerciseResult->id)
                ->update($data);
        }

        event(new ExerciseResultUpdated($exerciseId, $user));
    }
}
