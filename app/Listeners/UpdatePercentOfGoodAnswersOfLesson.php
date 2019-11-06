<?php

namespace App\Listeners;

use App\Events\LessonEventInterface;
use App\Models\Lesson;
use App\Models\User;
use App\Structures\UserExerciseRepository;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdatePercentOfGoodAnswersOfLesson implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * @var UserExerciseRepository
     */
    private $userExerciseRepository;

    /**
     * UpdatePercentOfGoodAnswersOfLesson constructor.
     * @param UserExerciseRepository $userExerciseRepository
     */
    public function __construct(UserExerciseRepository $userExerciseRepository)
    {
        $this->userExerciseRepository = $userExerciseRepository;
    }

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
        $userExercisesOfALesson = $this->userExerciseRepository->fetchUserExercisesOfLesson($user, $lesson->id);
        $userExercisesCount = $userExercisesOfALesson->count();

        // calculate percent_of_good_answers of a lesson
        if ($userExercisesCount) {
            $percentOfGoodAnswersSum = $userExercisesOfALesson->sum('percent_of_good_answers');
            $percentOfGoodAnswersOfLesson = round($percentOfGoodAnswersSum / $userExercisesCount);
        } else {
            $percentOfGoodAnswersOfLesson = 0;
        }

        // update percent_of_good_answers of a lesson
        $subscriber = $lesson->subscribedUsers()->where('lesson_user.user_id', $user->id)->first();
        $subscriber->pivot->percent_of_good_answers = $percentOfGoodAnswersOfLesson;
        $subscriber->pivot->save();

        // recursively update for each parent lesson
        foreach ($lesson->parentLessons as $parentLesson) {
            $this->updatePercentOfGoodAnswersOfLesson($parentLesson, $user);
        }
    }
}
