<?php

namespace Tests\Unit\Services;

use App\Services\PointsCalculator;
use Carbon\Carbon;

class PointsCalculatorTest extends \TestCase
{
    // calculatePoints

    private PointsCalculator $pointsCalculator;

    public function setUp(): void
    {
        parent::setUp();
        $this->pointsCalculator = new PointsCalculator();
    }

    public function pointsProvider()
    {
        return [
            ['percent_of_good_answers' => 100, 'expected_points' => 1],
            ['percent_of_good_answers' => 80, 'expected_points' => 20],
            ['percent_of_good_answers' => 50, 'expected_points' => 50],
            ['percent_of_good_answers' => 20, 'expected_points' => 80],
            ['percent_of_good_answers' => 1, 'expected_points' => 99],
            ['percent_of_good_answers' => 0, 'expected_points' => 100],
        ];
    }

    /**
     * @test
     * @dataProvider pointsProvider
     * @param int $percentOfGoodAnswers
     * @param int $expectedPoints
     * @throws \Exception
     */
    public function itShould_calculatePoints(int $percentOfGoodAnswers, int $expectedPoints)
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();
        $this->createExerciseResult(
            [
                'user_id' => $user->id,
                'exercise_id' => $exercise->id,
                'percent_of_good_answers' => $percentOfGoodAnswers,
                // do not leave these two empty, to avoid a '100' returned for no answers at all
                'number_of_good_answers' => 1,
                'number_of_bad_answers' => 1,
            ]
        );
        $userExercise = $this->createUserExercise($user, $exercise);

        $result = $this->pointsCalculator->calculatePoints($userExercise);

        $this->assertEquals($expectedPoints, $result);
    }

    /** @test */
    public function itShould_calculatePoints_goodAndBadAnswersToday_goodMostRecent()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();
        $this->createExerciseResult(
            [
                'user_id' => $user->id,
                'exercise_id' => $exercise->id,
                'percent_of_good_answers' => 50,
                'latest_good_answer' => Carbon::today()->addMinute(),
                'latest_bad_answer' => Carbon::today(),
                // do not leave these two empty, to avoid a '100' returned for no answers at all
                'number_of_good_answers' => 1,
                'number_of_bad_answers' => 1,
            ]
        );
        $userExercise = $this->createUserExercise($user, $exercise);

        $result = $this->pointsCalculator->calculatePoints($userExercise);

        $this->assertEquals(0, $result);
    }

    /** @test */
    public function itShould_calculatePoints_goodAnswerToday_noBadAnswer()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();
        $this->createExerciseResult(
            [
                'user_id' => $user->id,
                'exercise_id' => $exercise->id,
                'percent_of_good_answers' => 50,
                'latest_good_answer' => Carbon::today(),
                // do not leave these two empty, to avoid a '100' returned for no answers at all
                'number_of_good_answers' => 1,
                'number_of_bad_answers' => 1,
            ]
        );
        $userExercise = $this->createUserExercise($user, $exercise);

        $result = $this->pointsCalculator->calculatePoints($userExercise);

        $this->assertEquals(0, $result);
    }

    /** @test */
    public function itShould_calculatePoints_goodAnswerToday_badAnswerYesterday()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();
        $this->createExerciseResult(
            [
                'user_id' => $user->id,
                'exercise_id' => $exercise->id,
                'percent_of_good_answers' => 50,
                'latest_good_answer' => Carbon::today(),
                'latest_bad_answer' => Carbon::yesterday(),
                // do not leave these two empty, to avoid a '100' returned for no answers at all
                'number_of_good_answers' => 1,
                'number_of_bad_answers' => 1,
            ]
        );
        $userExercise = $this->createUserExercise($user, $exercise);

        $result = $this->pointsCalculator->calculatePoints($userExercise);

        $this->assertEquals(0, $result);
    }

    public function justBadAnswersTodayProvider()
    {
        return [
            [$numberOfBadAnswersToday = 1, $points = 80],
            [$numberOfBadAnswersToday = 2, $points = 50],
            [$numberOfBadAnswersToday = 3, $points = 20],
            [$numberOfBadAnswersToday = 4, $points = 20],
        ];
    }

    /**
     * @test
     * @dataProvider justBadAnswersTodayProvider
     * @param int $numberOfBadAnswersToday
     * @param int $points
     * @throws \Exception
     */
    public function itShould_calculatePoints_badAnswersToday_noGoodAnswerToday(int $numberOfBadAnswersToday, int $points)
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();
        $this->createExerciseResult(
            [
                'user_id' => $user->id,
                'exercise_id' => $exercise->id,
                'percent_of_good_answers' => 50,
                'latest_bad_answer' => Carbon::today(),
                'number_of_bad_answers_today' => $numberOfBadAnswersToday,
                // do not leave these two empty, to avoid a '100' returned for no answers at all
                'number_of_good_answers' => 1,
                'number_of_bad_answers' => 1,
            ]
        );
        $userExercise = $this->createUserExercise($user, $exercise);

        $result = $this->pointsCalculator->calculatePoints($userExercise);

        $this->assertEquals($points, $result);
    }

    /** @test */
    public function itShould_calculatePoints_badAnswersToday_noGoodAnswerToday_max_exercise_bad_answers_per_day_reached()
    {
        $numberOfBadAnswersToday = 1;

        config(
            [
                'app.max_exercise_bad_answers_per_day' => $numberOfBadAnswersToday,
            ]
        );

        $user = $this->createUser();
        $exercise = $this->createExercise();
        $this->createExerciseResult(
            [
                'user_id' => $user->id,
                'exercise_id' => $exercise->id,
                'percent_of_good_answers' => 50,
                'latest_bad_answer' => Carbon::today(),
                'number_of_bad_answers_today' => $numberOfBadAnswersToday,
                // do not leave these two empty, to avoid a '100' returned for no answers at all
                'number_of_good_answers' => 1,
                'number_of_bad_answers' => 1,
            ]
        );
        $userExercise = $this->createUserExercise($user, $exercise);

        $result = $this->pointsCalculator->calculatePoints($userExercise);

        $this->assertEquals(0, $result);
    }

    /** @test */
    public function itShould_calculatePoints_badAnswersToday_noGoodAnswerToday_max_exercise_bad_answers_per_day_exceeded()
    {
        $numberOfBadAnswersToday = 1;

        config(
            [
                'app.max_exercise_bad_answers_per_day' => $numberOfBadAnswersToday,
            ]
        );

        $user = $this->createUser();
        $exercise = $this->createExercise();
        $this->createExerciseResult(
            [
                'user_id' => $user->id,
                'exercise_id' => $exercise->id,
                'percent_of_good_answers' => 50,
                'latest_bad_answer' => Carbon::today(),
                // might happen that exercise is served too many times if queue processing is delayed
                'number_of_bad_answers_today' => $numberOfBadAnswersToday + 1,
                // do not leave these two empty, to avoid a '100' returned for no answers at all
                'number_of_good_answers' => 1,
                'number_of_bad_answers' => 1,
            ]
        );
        $userExercise = $this->createUserExercise($user, $exercise);

        $result = $this->pointsCalculator->calculatePoints($userExercise);

        $this->assertEquals(0, $result);
    }

    /**
     * @test
     * @dataProvider justBadAnswersTodayProvider
     * @param int $numberOfBadAnswersToday
     * @param int $points
     * @throws \Exception
     */
    public function itShould_calculatePoints_badAnswersToday_goodAnswerYesterday(
        int $numberOfBadAnswersToday,
        int $points
    ) {
        $user = $this->createUser();
        $exercise = $this->createExercise();
        $this->createExerciseResult(
            [
                'user_id' => $user->id,
                'exercise_id' => $exercise->id,
                'percent_of_good_answers' => 50,
                'latest_bad_answer' => Carbon::today(),
                'latest_good_answer' => Carbon::yesterday(),
                'number_of_bad_answers_today' => $numberOfBadAnswersToday,
                // do not leave these two empty, to avoid a '100' returned for no answers at all
                'number_of_good_answers' => 1,
                'number_of_bad_answers' => 1,
            ]
        );
        $userExercise = $this->createUserExercise($user, $exercise);

        $result = $this->pointsCalculator->calculatePoints($userExercise);

        $this->assertEquals($points, $result);
    }

    public function badAnswersOnlyProvider()
    {
        return [
            [$numberOfBadAnswersToday = 1, $points = 200],
            [$numberOfBadAnswersToday = 2, $points = 200],
            [$numberOfBadAnswersToday = 3, $points = 0],
        ];
    }

    /**
     * @test
     * @dataProvider badAnswersOnlyProvider
     * @param int $numberOfBadAnswers
     * @param int $points
     * @throws \Exception
     */
    public function itShould_calculatePoints_badAnswersOnly(int $numberOfBadAnswers, int $points)
    {
        config(
            [
                'app.max_exercise_bad_answers_per_day' => 3,
            ]
        );

        $user = $this->createUser();
        $exercise = $this->createExercise();
        $this->createExerciseResult(
            [
                'user_id' => $user->id,
                'exercise_id' => $exercise->id,
                'percent_of_good_answers' => 0,
                'latest_bad_answer' => Carbon::today(),
                'number_of_bad_answers' => $numberOfBadAnswers,
                'number_of_bad_answers_today' => $numberOfBadAnswers,
                'number_of_good_answers' => 0,
                'number_of_good_answers_today' => 0,
            ]
        );
        $userExercise = $this->createUserExercise($user, $exercise);

        $result = $this->pointsCalculator->calculatePoints($userExercise);

        $this->assertEquals($points, $result);
    }

    public function justGoodAnswersProvider()
    {
        return [
            [$numberOfGoodAnswers = 0, $points = 100],
            [$numberOfGoodAnswers = 1, $points = 80],
            [$numberOfGoodAnswers = 2, $points = 50],
            [$numberOfGoodAnswers = 3, $points = 20],
            [$numberOfGoodAnswers = 4, $points = 1],
        ];
    }

    /**
     * @test
     * @dataProvider justGoodAnswersProvider
     * @param int $numberOfGoodAnswers
     * @param int $points
     * @throws \Exception
     */
    public function itShould_calculatePoints_goodAnswersOnlyButNoneToday(int $numberOfGoodAnswers, int $points)
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();
        $this->createExerciseResult(
            [
                'user_id' => $user->id,
                'exercise_id' => $exercise->id,
                'number_of_good_answers' => $numberOfGoodAnswers,
                'number_of_bad_answers' => 0,
            ]
        );
        $userExercise = $this->createUserExercise($user, $exercise);

        $result = $this->pointsCalculator->calculatePoints($userExercise);

        $this->assertEquals($points, $result);
    }

    /** @test */
    public function itShould_calculatePoints_noResult()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();
        $userExercise = $this->createUserExercise($user, $exercise);

        $result = $this->pointsCalculator->calculatePoints($userExercise);

        $this->assertEquals(100, $result);
    }
}
