<?php

namespace App\Listeners;

use App\Events\AnswerEvent;
use App\Models\Exercise;
use App\Models\Lesson;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdatePercentOfGoodAnswersOfLesson implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * @param AnswerEvent $event
     * @return void
     */
    public function handle(AnswerEvent $event)
    {
        $lesson = $event->exercise->lesson;
        $userId = $event->userId;

        $this->updatePercentOfGoodAnswersOfLesson($lesson, $userId);
    }

    /**
     * @param Lesson $lesson
     * @param int    $userId
     */
    private function updatePercentOfGoodAnswersOfLesson(Lesson $lesson, int $userId)
    {
        // will include exercises of child lessons
        $exercises = $lesson->all_exercises;

        // lessons only takes exercises from 1 level below, so if lesson has no exercises, that means its children also don't have any
        // so we can assume there is a chain of parents without exercises, which we should not update
        if (!$exercises->count()) {
            return;
        }

        $percentOfGoodAnswersTotal = 0;
        $exercises->each(function (Exercise $exercise) use (&$percentOfGoodAnswersTotal, $userId) {
            $percentOfGoodAnswersTotal += $exercise->percentOfGoodAnswersOfUser($userId);
        });

        $percentOfGoodAnswersOfLesson = round($percentOfGoodAnswersTotal / $exercises->count());

        $subscriber = $lesson->subscribers()->where('lesson_user.user_id', $userId)->first();
        $subscriber->pivot->percent_of_good_answers = $percentOfGoodAnswersOfLesson;
        $subscriber->pivot->save();

        // recursively update for each parent lesson
        foreach ($lesson->parentLessons as $parentLesson) {
            $this->updatePercentOfGoodAnswersOfLesson($parentLesson, $userId);
        }
    }
}
