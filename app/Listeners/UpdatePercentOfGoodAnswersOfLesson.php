<?php

namespace App\Listeners;

use App\Events\LessonEventInterface;
use App\Models\Exercise;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

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
        // will include exercises of child lessons
        $exercises = $lesson->all_exercises;

        // lessons only takes exercises from 1 level below, so if lesson has no exercises, that means its children also don't have any
        if (!$exercises->count()) {
            $subscriber = $lesson->subscribers()->where('lesson_user.user_id', $user->id)->first();
            $subscriber->pivot->percent_of_good_answers = 0;
            $subscriber->pivot->save();
            return;
        }

        // if lesson has at least one exercise, we need to calculate and store percent of good answers
        $percentOfGoodAnswersTotal = 0;
        $exercises->each(function (Exercise $exercise) use (&$percentOfGoodAnswersTotal, $user) {
            $percentOfGoodAnswersTotal += $exercise->percentOfGoodAnswersOfUser($user->id);
        });

        $percentOfGoodAnswersOfLesson = round($percentOfGoodAnswersTotal / $exercises->count());

        $subscriber = $lesson->subscribers()->where('lesson_user.user_id', $user->id)->first();
        $subscriber->pivot->percent_of_good_answers = $percentOfGoodAnswersOfLesson;
        $subscriber->pivot->save();

        // recursively update for each parent lesson
        foreach ($lesson->parentLessons as $parentLesson) {
            $this->updatePercentOfGoodAnswersOfLesson($parentLesson, $user);
        }
    }
}
