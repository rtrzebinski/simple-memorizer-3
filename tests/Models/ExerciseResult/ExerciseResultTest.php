<?php

namespace Tests\Models\ExerciseResult;

use App\Models\ExerciseResult\ExerciseResult;
use TestCase;

class ExerciseResultTest extends TestCase
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
    public function testItShould_updatePercentOfGoodAnswers(
        $numberOfGoodAnswers,
        $numberOfBadAnswers,
        $percentOfGoodAnswers
    ) {
        $exerciseResult = new ExerciseResult();

        $exerciseResult->number_of_good_answers = $numberOfGoodAnswers;
        $exerciseResult->number_of_bad_answers = $numberOfBadAnswers;

        $exerciseResult->updatePercentOfGoodAnswers();

        $this->assertEquals($percentOfGoodAnswers, $exerciseResult->percent_of_good_answers);
    }
}
