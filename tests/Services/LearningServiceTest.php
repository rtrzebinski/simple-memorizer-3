<?php

namespace Tests\Services;

use App\Services\LearningService;
use Carbon\Carbon;
use TestCase;
use App\Models\Exercise;
use App\Models\Lesson;

class LearningServiceTest extends TestCase
{
    /**
     * @var LearningService
     */
    private $learningService;

    public function setUp(): void
    {
        parent::setUp();
        $this->learningService = new LearningService();
    }

    // fetchRandomExerciseOfLesson

    /** @test */
    public function itShould_returnRandomExercise_exerciseOnlyHasAnswersOfAnotherUser()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);
        $lesson->subscribe($user);

        $this->createExerciseResult([
            'user_id' => $this->createUser()->id,
            'exercise_id' => $exercise->id,
            'percent_of_good_answers' => 10,
        ]);

        $this->assertExerciseCanWin($lesson, $user->id, $exercise->id);
    }

    /** @test */
    public function itShould_returnRandomExercise_onePossibleExercise()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->assertExerciseCanWin($lesson, $user->id, $exercise->id);
    }

    /** @test */
    public function itShould_returnRandomExercise_onePossibleExercise_withPrevious()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        $exerciseWithAnswer = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->createExerciseResult([
            'user_id' => $this->createUser()->id,
            'exercise_id' => $exerciseWithAnswer->id,
            'percent_of_good_answers' => 10,
        ]);

        $previousExercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->assertExerciseCanWin($lesson, $user->id, $exerciseWithAnswer->id, $previousExercise->id);
        $this->assertExerciseCanWin($lesson, $user->id, $previousExercise->id, $exerciseWithAnswer->id);
    }

    /** @test */
    public function itShould_returnRandomExercise_oneExcludedExerciseAndExcludedPrevious()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        // exercises is excluded
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);
        $this->createExerciseResult([
            'user_id' => $user->id,
            'exercise_id' => $exercise->id,
            'percent_of_good_answers' => 10,
            'number_of_good_answers' => 1,
            'number_of_good_answers_today' => 1,
            'latest_good_answer' => Carbon::today(),
        ]);

        // previous is excluded
        $previous = $this->createExercise(['lesson_id' => $lesson->id]);
        $this->createExerciseResult([
            'user_id' => $user->id,
            'exercise_id' => $previous->id,
            'percent_of_good_answers' => 10,
            'number_of_good_answers' => 1,
            'number_of_good_answers_today' => 1,
            'latest_good_answer' => Carbon::today(),
        ]);

        $result = $this->learningService->fetchRandomExerciseOfLesson($lesson, $user->id, $previous->id);

        $this->assertNull($result);
    }

    /** @test */
    public function itShould_returnRandomExercise_twoExcluded_withPreviousNotAnswered()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        $exercise1 = $this->createExercise(['lesson_id' => $lesson->id]);
        $this->createExerciseResult([
            'user_id' => $user->id,
            'exercise_id' => $exercise1->id,
            'percent_of_good_answers' => 10,
            'number_of_good_answers' => 1,
            'number_of_good_answers_today' => 1,
            'latest_good_answer' => Carbon::today(),
        ]);

        $exercise2 = $this->createExercise(['lesson_id' => $lesson->id]);
        $this->createExerciseResult([
            'user_id' => $user->id,
            'exercise_id' => $exercise2->id,
            'percent_of_good_answers' => 10,
            'number_of_good_answers' => 1,
            'number_of_good_answers_today' => 1,
            'latest_good_answer' => Carbon::today(),
        ]);

        $previousExercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $result = $this->learningService->fetchRandomExerciseOfLesson($lesson, $user->id, $previousExercise->id);

        $this->assertInstanceOf(Exercise::class, $result);
        $this->assertEquals($previousExercise->id, $result->id);
    }

    /** @test */
    public function itShould_returnRandomExercise_twoExcluded_withPreviousAnswered()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        $exercise1 = $this->createExercise(['lesson_id' => $lesson->id]);
        $this->createExerciseResult([
            'user_id' => $user->id,
            'exercise_id' => $exercise1->id,
            'percent_of_good_answers' => 10,
            'number_of_good_answers' => 1,
            'number_of_good_answers_today' => 1,
            'latest_good_answer' => Carbon::today(),
        ]);

        $exercise2 = $this->createExercise(['lesson_id' => $lesson->id]);
        $this->createExerciseResult([
            'user_id' => $user->id,
            'exercise_id' => $exercise2->id,
            'percent_of_good_answers' => 10,
            'number_of_good_answers' => 1,
            'number_of_good_answers_today' => 1,
            'latest_good_answer' => Carbon::today(),
        ]);

        $previousExercise = $this->createExercise(['lesson_id' => $lesson->id]);
        $this->createExerciseResult([
            'user_id' => $user->id,
            'exercise_id' => $previousExercise->id,
            'percent_of_good_answers' => 10,
        ]);

        $result = $this->learningService->fetchRandomExerciseOfLesson($lesson, $user->id, $previousExercise->id);

        $this->assertInstanceOf(Exercise::class, $result);
        $this->assertEquals($previousExercise->id, $result->id);
    }

    /** @test */
    public function itShould_returnRandomExercise_twoExcluded_withPreviousExcluded()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        $exercise1 = $this->createExercise(['lesson_id' => $lesson->id]);
        $this->createExerciseResult([
            'user_id' => $user->id,
            'exercise_id' => $exercise1->id,
            'percent_of_good_answers' => 10,
            'number_of_good_answers' => 1,
            'number_of_good_answers_today' => 1,
            'latest_good_answer' => Carbon::today(),
        ]);

        $exercise2 = $this->createExercise(['lesson_id' => $lesson->id]);
        $this->createExerciseResult([
            'user_id' => $user->id,
            'exercise_id' => $exercise2->id,
            'percent_of_good_answers' => 10,
            'number_of_good_answers' => 1,
            'number_of_good_answers_today' => 1,
            'latest_good_answer' => Carbon::today(),
        ]);

        $previousExercise = $this->createExercise(['lesson_id' => $lesson->id]);
        $this->createExerciseResult([
            'user_id' => $user->id,
            'exercise_id' => $previousExercise->id,
            'percent_of_good_answers' => 10,
            'number_of_good_answers' => 1,
            'number_of_good_answers_today' => 1,
            'latest_good_answer' => Carbon::today(),
        ]);

        $result = $this->learningService->fetchRandomExerciseOfLesson($lesson, $user->id, $previousExercise->id);

        $this->assertNull($result);
    }

    /** @test */
    public function itShould_returnRandomExercise_twoPossibleExercises()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        $exerciseWithAnswer = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->createExerciseResult([
            'user_id' => $this->createUser()->id,
            'exercise_id' => $exerciseWithAnswer->id,
            'percent_of_good_answers' => 10,
        ]);

        $exerciseWithNoAnswer = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->assertExerciseCanWin($lesson, $user->id, $exerciseWithAnswer->id);
        $this->assertExerciseCanWin($lesson, $user->id, $exerciseWithNoAnswer->id);
    }

    /** @test */
    public function itShould_returnRandomExercise_twoPossibleExercises_withPrevious()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        $exerciseWithAnswer = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->createExerciseResult([
            'user_id' => $this->createUser()->id,
            'exercise_id' => $exerciseWithAnswer->id,
            'percent_of_good_answers' => 10,
        ]);

        $exerciseWithNoAnswer = $this->createExercise(['lesson_id' => $lesson->id]);
        $previousExercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->assertExerciseCanWin($lesson, $user->id, $exerciseWithAnswer->id, $previousExercise->id);
        $this->assertExerciseCanWin($lesson, $user->id, $exerciseWithNoAnswer->id, $previousExercise->id);
        $this->assertPreviousExerciseCanNotWin($lesson, $user->id, $previousExercise->id);
    }

    /** @test */
    public function itShould_returnRandomExercise_fourPossibleExercises()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        $exerciseWithAnswer1 = $this->createExercise(['lesson_id' => $lesson->id]);
        $this->createExerciseResult([
            'user_id' => $this->createUser()->id,
            'exercise_id' => $exerciseWithAnswer1->id,
            'percent_of_good_answers' => 10,
        ]);

        $exerciseWithAnswer2 = $this->createExercise(['lesson_id' => $lesson->id]);
        $this->createExerciseResult([
            'user_id' => $this->createUser()->id,
            'exercise_id' => $exerciseWithAnswer2->id,
            'percent_of_good_answers' => 10,
        ]);

        $exerciseWithNoAnswer1 = $this->createExercise(['lesson_id' => $lesson->id]);

        $exerciseWithNoAnswer2 = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->assertExerciseCanWin($lesson, $user->id, $exerciseWithAnswer1->id);
        $this->assertExerciseCanWin($lesson, $user->id, $exerciseWithAnswer2->id);
        $this->assertExerciseCanWin($lesson, $user->id, $exerciseWithNoAnswer1->id);
        $this->assertExerciseCanWin($lesson, $user->id, $exerciseWithNoAnswer2->id);
    }

    /** @test */
    public function itShould_returnRandomExercise_fourPossibleExercises_withPrevious()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        $exerciseWithAnswer1 = $this->createExercise(['lesson_id' => $lesson->id]);
        $this->createExerciseResult([
            'user_id' => $this->createUser()->id,
            'exercise_id' => $exerciseWithAnswer1->id,
            'percent_of_good_answers' => 10,
        ]);

        $exerciseWithAnswer2 = $this->createExercise(['lesson_id' => $lesson->id]);
        $this->createExerciseResult([
            'user_id' => $this->createUser()->id,
            'exercise_id' => $exerciseWithAnswer2->id,
            'percent_of_good_answers' => 10,
        ]);

        $exerciseWithNoAnswer1 = $this->createExercise(['lesson_id' => $lesson->id]);

        $exerciseWithNoAnswer2 = $this->createExercise(['lesson_id' => $lesson->id]);

        $previousExercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->assertExerciseCanWin($lesson, $user->id, $exerciseWithAnswer1->id, $previousExercise->id);
        $this->assertExerciseCanWin($lesson, $user->id, $exerciseWithAnswer2->id, $previousExercise->id);
        $this->assertExerciseCanWin($lesson, $user->id, $exerciseWithNoAnswer1->id, $previousExercise->id);
        $this->assertExerciseCanWin($lesson, $user->id, $exerciseWithNoAnswer2->id, $previousExercise->id);
        $this->assertPreviousExerciseCanNotWin($lesson, $user->id, $previousExercise->id);
    }

    /** @test */
    public function itShould_returnRandomExercise_goodAndBadAnswerToday()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        $exerciseWithAnswer = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->createExerciseResult([
            'user_id' => $this->createUser()->id,
            'exercise_id' => $exerciseWithAnswer->id,
            'percent_of_good_answers' => 10,
            'latest_good_answer' => Carbon::now(),
            'latest_bad_answer' => Carbon::now(),
        ]);

        $exerciseWithNoAnswer = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->assertExerciseCanWin($lesson, $user->id, $exerciseWithAnswer->id);
        $this->assertExerciseCanWin($lesson, $user->id, $exerciseWithNoAnswer->id);
    }

    /** @test */
    public function itShould_returnRandomExercise_goodAnswerToday()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        $exerciseWithAnswer = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->createExerciseResult([
            'user_id' => $this->createUser()->id,
            'exercise_id' => $exerciseWithAnswer->id,
            'percent_of_good_answers' => 10,
            'latest_good_answer' => Carbon::now(),
        ]);

        $exerciseWithNoAnswer = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->assertExerciseCanWin($lesson, $user->id, $exerciseWithAnswer->id);
        $this->assertExerciseCanWin($lesson, $user->id, $exerciseWithNoAnswer->id);
    }

    /** @test */
    public function itShould_returnRandomExercise_badAnswerToday()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        $exerciseWithAnswer = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->createExerciseResult([
            'user_id' => $this->createUser()->id,
            'exercise_id' => $exerciseWithAnswer->id,
            'percent_of_good_answers' => 10,
            'latest_bad_answer' => Carbon::now(),
        ]);

        $exerciseWithNoAnswer = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->assertExerciseCanWin($lesson, $user->id, $exerciseWithAnswer->id);
        $this->assertExerciseCanWin($lesson, $user->id, $exerciseWithNoAnswer->id);
    }

    /** @test */
    public function itShould_notReturnRandomExercise_lessonHasNoExercises()
    {
        $lesson = $this->createLesson();

        $result = $this->learningService->fetchRandomExerciseOfLesson($lesson, $this->createUser()->id);

        $this->assertNull($result);
    }

    /** @test */
    public function itShould_notReturnRandomExercise_lessonOnlyHasPreviousExercise()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $previousExercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $result = $this->learningService->fetchRandomExerciseOfLesson($lesson, $user->id, $previousExercise->id);

        $this->assertNull($result);
    }

    /** @test */
    public function itShould_returnRandomExercise_bidirectional()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribe($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $exercise = $lesson->exercises[0];

        $subscriber = $lesson->subscribedUsers()
            ->where('lesson_user.user_id', '=', $user->id)
            ->first();

        // set subscription as bidirectional
        $subscriber->pivot->bidirectional = true;
        $subscriber->pivot->save();

        // 1000 retries is more than enough
        $counter = 1000;
        do {
            if (!$counter--) {
                $this->fail('Bidirectional flag does not work - unable to fetch reverted exercise.');
            }
            $result = $this->learningService->fetchRandomExerciseOfLesson($lesson, $user->id);
            // just to avoid "This test did not perform any assertions" warning
            $this->assertTrue(true);
        } while ($result->answer != $exercise->question);
    }

    /** @test */
    public function itShould_returnRandomExercise_notBidirectional()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribe($user);
        $this->createExercisesRequiredToLearnLesson($lesson->id);
        $exercise = $lesson->exercises[0];

        $subscriber = $lesson->subscribedUsers()
            ->where('lesson_user.user_id', '=', $user->id)
            ->first();

        // set subscription as bidirectional
        $subscriber->pivot->bidirectional = false;
        $subscriber->pivot->save();

        $counter = 10;
        do {
            if (!$counter--) {
                $this->fail('Bidirectional flag does not work - reverted exercise was fetched while it should not.');
            }
            $result = $this->learningService->fetchRandomExerciseOfLesson($lesson, $user->id);
            // just to avoid "This test did not perform any assertions" warning
            $this->assertTrue(true);
        } while ($result->answer == $exercise->question);
    }

    private function assertExerciseCanWin(Lesson $lesson, int $userId, int $exerciseId, int $previousId = null)
    {
        // try 1000 times - it does not bother us if this number is high,
        // as exercise is likely to be returned in one of first attempts anyway
        // keeping this counter big will prevent from occasional false positives
        $counter = 1000;
        do {
            if (!$counter--) {
                $this->fail('Unable to fetch random exercise.');
            }
            $result = $this->learningService->fetchRandomExerciseOfLesson($lesson, $userId, $previousId);
            $this->assertInstanceOf(Exercise::class, $result);
        } while ($result->id != $exerciseId);
    }

    private function assertPreviousExerciseCanNotWin(Lesson $lesson, int $userId, int $previousId)
    {
        // try 5 times - should be enough to often find a failing case
        // it won't be 100% accurate, but we don't want this check to be too slow as well
        $counter = 5;
        do {
            if (!$counter--) {
                // all good - previous exercise was never returned
                return;
            }
            $result = $this->learningService->fetchRandomExerciseOfLesson($lesson, $userId, $previousId);
            $this->assertTrue($result->id != $previousId, 'Previous exercise was returned');
        } while (1);
    }

    // calculatePoints

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
        $exerciseResult = $this->createExerciseResult([
            'user_id' => $this->createUser()->id,
            'percent_of_good_answers' => $percentOfGoodAnswers,
        ]);

        $result = $this->learningService->calculatePoints($exerciseResult);

        $this->assertEquals($expectedPoints, $result);
    }

    /** @test */
    public function itShould_calculatePoints_goodAndBadAnswersToday_goodMostRecent()
    {
        $exerciseResult = $this->createExerciseResult([
            'user_id' => $this->createUser()->id,
            'percent_of_good_answers' => 50,
            'latest_good_answer' => Carbon::today()->addMinute(),
            'latest_bad_answer' => Carbon::today(),
        ]);

        $result = $this->learningService->calculatePoints($exerciseResult);

        $this->assertEquals(0, $result);
    }

    /** @test */
    public function itShould_calculatePoints_goodAndBadAnswersToday_badMostRecent()
    {
        $exerciseResult = $this->createExerciseResult([
            'user_id' => $this->createUser()->id,
            'percent_of_good_answers' => 50,
            'latest_good_answer' => Carbon::today(),
            'latest_bad_answer' => Carbon::today()->addMinute(),
        ]);

        $result = $this->learningService->calculatePoints($exerciseResult);

        $this->assertEquals(100, $result);
    }

    /** @test */
    public function itShould_calculatePoints_goodAnswerToday_noBadAnswer()
    {
        $exerciseResult = $this->createExerciseResult([
            'user_id' => $this->createUser()->id,
            'percent_of_good_answers' => 50,
            'latest_good_answer' => Carbon::today(),
        ]);

        $result = $this->learningService->calculatePoints($exerciseResult);

        $this->assertEquals(0, $result);
    }

    /** @test */
    public function itShould_calculatePoints_goodAnswerToday_badAnswerYesterday()
    {
        $exerciseResult = $this->createExerciseResult([
            'user_id' => $this->createUser()->id,
            'percent_of_good_answers' => 50,
            'latest_good_answer' => Carbon::today(),
            'latest_bad_answer' => Carbon::yesterday(),
        ]);

        $result = $this->learningService->calculatePoints($exerciseResult);

        $this->assertEquals(0, $result);
    }

    /** @test */
    public function itShould_calculatePoints_badAnswersToday_noGoodAnswer()
    {
        $exerciseResult = $this->createExerciseResult([
            'user_id' => $this->createUser()->id,
            'percent_of_good_answers' => 50,
            'latest_bad_answer' => Carbon::today(),
        ]);

        $result = $this->learningService->calculatePoints($exerciseResult);

        $this->assertEquals(100, $result);
    }

    /** @test */
    public function itShould_calculatePoints_badAnswersToday_goodAnswerYesterday()
    {
        $exerciseResult = $this->createExerciseResult([
            'user_id' => $this->createUser()->id,
            'percent_of_good_answers' => 50,
            'latest_bad_answer' => Carbon::today(),
            'latest_good_answer' => Carbon::yesterday(),
        ]);

        $result = $this->learningService->calculatePoints($exerciseResult);

        $this->assertEquals(100, $result);
    }

    /** @test */
    public function itShould_calculatePoints_noResult()
    {
        $result = $this->learningService->calculatePoints(null);

        $this->assertEquals(100, $result);
    }
}
