<?php

namespace Tests\Listeners;

use App\Events\GoodAnswer;
use App\Listeners\UpdatePercentOfGoodAnswersOfExercise;
use App\Models\ExerciseResult;

class UpdatePercentOfGoodAnswersOfExerciseTest extends \TestCase
{
    public function answersProvider()
    {
        return [
            [0, 0, 0],
            [1, 19, 5],
            [1, 9, 10],
            [3, 17, 15],
            [2, 8, 20],
            [5, 15, 25],
            [3, 7, 30],
            [7, 13, 35],
            [4, 6, 40],
            [9, 11, 45],
            [5, 5, 50],
            [11, 9, 55],
            [6, 4, 60],
            [13, 7, 65],
            [7, 3, 70],
            [15, 5, 75],
            [8, 2, 80],
            [17, 3, 85],
            [9, 1, 90],
            [19, 1, 95],
            [10, 0, 100],
        ];
    }

    /**
     * @dataProvider answersProvider
     * @param $numberOfGoodAnswers
     * @param $numberOfBadAnswers
     * @param $percentOfGoodAnswers
     */
    public function testItShould_updatePercentOfGoodAnswersOfExercise(
        $numberOfGoodAnswers,
        $numberOfBadAnswers,
        $percentOfGoodAnswers
    ) {
        $exercise = $this->createExercise();
        $user = $this->createUser();

        $exerciseResult = $this->createExerciseResult([
            'exercise_id' => $exercise->id,
            'user_id' => $user->id,
            'number_of_good_answers' => $numberOfGoodAnswers,
            'number_of_bad_answers' => $numberOfBadAnswers,
        ]);

        $listener = new UpdatePercentOfGoodAnswersOfExercise();
        $event = new GoodAnswer($exercise, $user->id);
        $listener->handle($event);

        $this->assertEquals($percentOfGoodAnswers, $exerciseResult->fresh()->percent_of_good_answers);
    }
}
