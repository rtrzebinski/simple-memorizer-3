<?php

namespace App\Listeners;

use App\Events\AnswerEvent;
use App\Models\Exercise;
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

        $percentOfGoodAnswersTotal = 0;

        $exercises = $lesson->all_exercises;

        $exercises->each(function (Exercise $exercise) use (&$percentOfGoodAnswersTotal, $userId) {
            $percentOfGoodAnswersTotal += $exercise->percentOfGoodAnswersOfUser($userId);
        });

        $percentOfGoodAnswersOfLesson = round($percentOfGoodAnswersTotal / $exercises->count());

        $subscriber = $lesson->subscribers()->where('lesson_user.user_id', $userId)->first();
        $subscriber->pivot->percent_of_good_answers = $percentOfGoodAnswersOfLesson;
        $subscriber->pivot->save();
    }
}
