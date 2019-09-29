<?php

namespace Tests\Listeners;

use App\Events\GoodAnswer;
use App\Listeners\UpdatePercentOfGoodAnswersOfLesson;

class UpdatePercentOfGoodAnswersOfLessonTest extends \TestCase
{
    public function updatePercentOfGoodAnswersOfLessonDataProvider()
    {
        return [
            [$exercise1percentOfGoodAnswers = 10, $exercise2percentOfGoodAnswers = 10, $expectedResult = 10],
            [$exercise1percentOfGoodAnswers = 0, $exercise2percentOfGoodAnswers = 100, $expectedResult = 50],
            [$exercise1percentOfGoodAnswers = 0, $exercise2percentOfGoodAnswers = 0, $expectedResult = 0],
            [$exercise1percentOfGoodAnswers = 100, $exercise2percentOfGoodAnswers = 100, $expectedResult = 100],
        ];
    }

    /**
     * @test
     * @dataProvider updatePercentOfGoodAnswersOfLessonDataProvider
     * @param int $exercise1percentOfGoodAnswers
     * @param int $exercise2percentOfGoodAnswers
     * @param int $expectedResult
     * @throws \Exception
     */
    public function itShould_updatePercentOfGoodAnswersOfLesson(int $exercise1percentOfGoodAnswers, int $exercise2percentOfGoodAnswers, int $expectedResult)
    {
        $user = $this->createUser();

        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);
        $this->createExerciseResult([
            'user_id' => $user->id,
            'exercise_id' => $exercise->id,
            'percent_of_good_answers' => $exercise1percentOfGoodAnswers,
        ]);

        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);
        $this->createExerciseResult([
            'user_id' => $user->id,
            'exercise_id' => $exercise->id,
            'percent_of_good_answers' => $exercise2percentOfGoodAnswers,
        ]);

        $listener = new UpdatePercentOfGoodAnswersOfLesson();
        $event = new GoodAnswer($exercise, $user->id);
        $listener->handle($event);

        $this->assertEquals($expectedResult, $lesson->percentOfGoodAnswersOfUser($user->id));
    }
}
