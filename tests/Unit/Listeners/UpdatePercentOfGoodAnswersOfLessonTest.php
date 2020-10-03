<?php

namespace Tests\Unit\Listeners;

use App\Events\ExerciseGoodAnswer;
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
    public function itShould_updatePercentOfGoodAnswersOfLesson(
        int $exercise1percentOfGoodAnswers,
        int $exercise2percentOfGoodAnswers,
        int $expectedResult
    ) {
        $user = $this->createUser();

        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);
        $this->createExerciseResult(
            [
                'user_id' => $user->id,
                'exercise_id' => $exercise->id,
                'percent_of_good_answers' => $exercise1percentOfGoodAnswers,
            ]
        );

        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);
        $this->createExerciseResult(
            [
                'user_id' => $user->id,
                'exercise_id' => $exercise->id,
                'percent_of_good_answers' => $exercise2percentOfGoodAnswers,
            ]
        );

        $listener = new UpdatePercentOfGoodAnswersOfLesson();
        $event = new ExerciseGoodAnswer($exercise->id, $user);
        $listener->handle($event);

        $this->assertEquals($expectedResult, $this->percentOfGoodAnswersOfLesson($lesson, $user->id));
    }

    /** @test */
    public function itShould_updatePercentOfGoodAnswersOfLesson_exerciseWithoutAnswer()
    {
        $user = $this->createUser();

        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);
        $this->createExerciseResult(
            [
                'user_id' => $user->id,
                'exercise_id' => $exercise->id,
                'percent_of_good_answers' => 100,
            ]
        );

        // this should be considered 0%, as it has no answers
        $this->createExercise(['lesson_id' => $lesson->id]);

        $listener = new UpdatePercentOfGoodAnswersOfLesson();
        $event = new ExerciseGoodAnswer($exercise->id, $user);
        $listener->handle($event);

        // (100 + 0) / 2 = 50
        $this->assertEquals(50, $this->percentOfGoodAnswersOfLesson($lesson, $user->id));
    }

    /** @test */
    public function itShould_updatePercentOfGoodAnswersOfLesson_alsoUpdateParents()
    {
        $user = $this->createUser();

        $grandparentLesson = $this->createLesson();
        $grandparentLesson->subscribe($user);

        $parentLesson = $this->createLesson();
        $parentLesson->subscribe($user);
        $grandparentLesson->childLessons()->attach($parentLesson);

        $childLesson = $this->createLesson();
        $childLesson->subscribe($user);
        $parentLesson->childLessons()->attach($childLesson);

        $this->assertEquals(0, $this->percentOfGoodAnswersOfLesson($parentLesson, $user->id));
        $this->assertEquals(0, $this->percentOfGoodAnswersOfLesson($childLesson, $user->id));

        $exercise = $this->createExercise(['lesson_id' => $childLesson->id]);
        $this->createExerciseResult(
            [
                'user_id' => $user->id,
                'exercise_id' => $exercise->id,
                'percent_of_good_answers' => 50,
            ]
        );

        $exercise = $this->createExercise(['lesson_id' => $childLesson->id]);
        $this->createExerciseResult(
            [
                'user_id' => $user->id,
                'exercise_id' => $exercise->id,
                'percent_of_good_answers' => 100,
            ]
        );

        $this->assertEquals(0, $this->percentOfGoodAnswersOfLesson($childLesson, $user->id));
        $this->assertEquals(0, $this->percentOfGoodAnswersOfLesson($parentLesson, $user->id));
        $this->assertEquals(0, $this->percentOfGoodAnswersOfLesson($grandparentLesson, $user->id));

        // (50 + 100) / 2 = 75

        $listener = new UpdatePercentOfGoodAnswersOfLesson();
        $event = new ExerciseGoodAnswer($exercise->id, $user);
        $listener->handle($event);

        $this->assertEquals(75, $this->percentOfGoodAnswersOfLesson($childLesson, $user->id));
        $this->assertEquals(75, $this->percentOfGoodAnswersOfLesson($parentLesson, $user->id));
        $this->assertEquals(0, $this->percentOfGoodAnswersOfLesson($grandparentLesson, $user->id));
    }
}
