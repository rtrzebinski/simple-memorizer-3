<?php

namespace App\Listeners;

use App\Events\LessonEventInterface;
use App\Models\Lesson;
use App\Models\User;
use App\Structures\UserExercise\AuthenticatedUserExerciseRepository;
use Carbon\Carbon;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class UpdatePercentOfGoodAnswersOfLesson implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * @param LessonEventInterface $event
     * @return void
     */
    public function handle(LessonEventInterface $event)
    {
        $lesson = $event->lesson();
        $user = $event->user();

        $this->updatePercentOfGoodAnswersOfLesson($lesson, $user);
    }

    /**
     * @param Lesson $lesson
     * @param User   $user
     */
    private function updatePercentOfGoodAnswersOfLesson(Lesson $lesson, User $user)
    {
        // fetch user exercises of a lesson
        $userExerciseRepository = new AuthenticatedUserExerciseRepository($user);
        $userExercises = $userExerciseRepository->fetchUserExercisesOfLesson($lesson->id);

        if ($userExercisesCount = $userExercises->count()) {
            // calculate percent_of_good_answers of a lesson
            $percentOfGoodAnswersSum = $userExercises->sum('percent_of_good_answers');
            $percentOfGoodAnswersOfLesson = round($percentOfGoodAnswersSum / $userExercisesCount);

            // update percent_of_good_answers of a lesson
            DB::table('lesson_user')
                ->where('lesson_user.lesson_id', '=', $lesson->id)
                ->where('lesson_user.user_id', '=', $user->id)
                ->update([
                    'percent_of_good_answers' => $percentOfGoodAnswersOfLesson,
                    'updated_at' => Carbon::now(),

                ]);
        } else {
            // no answers - set percent_of_good_answers to 0
            DB::table('lesson_user')
                ->where('lesson_user.lesson_id', '=', $lesson->id)
                ->where('lesson_user.user_id', '=', $user->id)
                ->update([
                    'percent_of_good_answers' => 0,
                    'updated_at' => Carbon::now(),
                ]);
        }

        // recursively run for each parent lesson
        foreach ($lesson->parentLessons as $parentLesson) {
            $this->updatePercentOfGoodAnswersOfLesson($parentLesson, $user);
        }
    }
}
