<?php

namespace Tests\Listeners;

use App\Events\ExerciseGoodAnswer;
use App\Events\ExercisePercentOfGoodAnswersUpdated;
use App\Listeners\UpdatePercentOfGoodAnswersOfExercise;

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
            [1, 0, 20],
            [2, 0, 40],
            [3, 0, 60],
            [4, 0, 80],
        ];
    }

    /**
     * @test
     * @dataProvider answersProvider
     * @param $numberOfGoodAnswers
     * @param $numberOfBadAnswers
     * @param $percentOfGoodAnswers
     */
    public function itShould_updatePercentOfGoodAnswersOfExercise(
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

        $this->expectsEvents(ExercisePercentOfGoodAnswersUpdated::class);

        $listener = new UpdatePercentOfGoodAnswersOfExercise();
        $event = new ExerciseGoodAnswer($exercise->id, $user);
        $listener->handle($event);

        $this->assertEquals($percentOfGoodAnswers, $exerciseResult->fresh()->percent_of_good_answers);
    }
}
