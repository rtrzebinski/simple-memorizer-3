<?php

namespace Tests\Listeners;

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
        $event = new ExerciseGoodAnswer($exercise, $user);
        $listener->handle($event);

        $this->assertEquals($expectedResult, $lesson->percentOfGoodAnswers($user->id));
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

        $this->assertEquals(0, $parentLesson->percentOfGoodAnswers($user->id));
        $this->assertEquals(0, $childLesson->percentOfGoodAnswers($user->id));

        $exercise = $this->createExercise(['lesson_id' => $childLesson->id]);
        $this->createExerciseResult([
            'user_id' => $user->id,
            'exercise_id' => $exercise->id,
            'percent_of_good_answers' => 50,
        ]);

        $exercise = $this->createExercise(['lesson_id' => $childLesson->id]);
        $this->createExerciseResult([
            'user_id' => $user->id,
            'exercise_id' => $exercise->id,
            'percent_of_good_answers' => 100,
        ]);

        $this->assertEquals(0, $childLesson->percentOfGoodAnswers($user->id));
        $this->assertEquals(0, $parentLesson->percentOfGoodAnswers($user->id));
        $this->assertEquals(0, $grandparentLesson->percentOfGoodAnswers($user->id));

        // (50 + 100) / 2 = 75

        $listener = new UpdatePercentOfGoodAnswersOfLesson();
        $event = new ExerciseGoodAnswer($exercise, $user);
        $listener->handle($event);

        $this->assertEquals(75, $childLesson->percentOfGoodAnswers($user->id));
        $this->assertEquals(75, $parentLesson->percentOfGoodAnswers($user->id));
        $this->assertEquals(0, $grandparentLesson->percentOfGoodAnswers($user->id));
    }
}
