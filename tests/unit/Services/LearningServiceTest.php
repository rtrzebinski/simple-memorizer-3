<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\LearningService;
use App\Structures\AuthenticatedUserExerciseRepository;
use App\Structures\UserExercise;
use App\Structures\UserExerciseRepository;
use App\Structures\UserLesson;
use Carbon\Carbon;
use TestCase;

class LearningServiceTest extends TestCase
{
    private LearningService $learningService;

    public function setUp(): void
    {
        parent::setUp();
        $this->learningService = new LearningService(new UserExerciseRepository(), new AuthenticatedUserExerciseRepository());
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

        $subscriber = $lesson->subscribedUsers()
            ->where('lesson_user.user_id', '=', $user->id)
            ->first();

        // set subscription as bidirectional
        $subscriber->pivot->bidirectional = true;
        $subscriber->pivot->save();

        $userLesson = $this->createUserLesson($user, $lesson, $isBidirectional = false);
        $this->assertExerciseCanWin($userLesson, $user, $exercise->id);
    }

    /** @test */
    public function itShould_returnRandomExercise_onePossibleExercise()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $userLesson = $this->createUserLesson($user, $lesson, $isBidirectional = false);
        $this->assertExerciseCanWin($userLesson, $user, $exercise->id);
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

        $userLesson = $this->createUserLesson($user, $lesson, $isBidirectional = false);
        $this->assertExerciseCanWin($userLesson, $user, $exerciseWithAnswer->id, $previousExercise->id);
        $this->assertExerciseCanWin($userLesson, $user, $previousExercise->id, $exerciseWithAnswer->id);
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

        $userLesson = $this->createUserLesson($user, $lesson, $isBidirectional = false);
        $result = $this->learningService->fetchRandomExerciseOfLesson($userLesson, $user, $previous->id);

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

        $userLesson = $this->createUserLesson($user, $lesson, $isBidirectional = false);
        $result = $this->learningService->fetchRandomExerciseOfLesson($userLesson, $user, $previousExercise->id);

        $this->assertInstanceOf(UserExercise::class, $result);
        $this->assertEquals($previousExercise->id, $result->exercise_id);
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

        $userLesson = $this->createUserLesson($user, $lesson, $isBidirectional = false);
        $result = $this->learningService->fetchRandomExerciseOfLesson($userLesson, $user, $previousExercise->id);

        $this->assertInstanceOf(UserExercise::class, $result);
        $this->assertEquals($previousExercise->id, $result->exercise_id);
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

        $userLesson = $this->createUserLesson($user, $lesson, $isBidirectional = false);
        $result = $this->learningService->fetchRandomExerciseOfLesson($userLesson, $user, $previousExercise->id);

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

        $userLesson = $this->createUserLesson($user, $lesson, $isBidirectional = false);
        $this->assertExerciseCanWin($userLesson, $user, $exerciseWithAnswer->id);
        $this->assertExerciseCanWin($userLesson, $user, $exerciseWithNoAnswer->id);
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

        $userLesson = $this->createUserLesson($user, $lesson, $isBidirectional = false);
        $this->assertExerciseCanWin($userLesson, $user, $exerciseWithAnswer->id, $previousExercise->id);
        $this->assertExerciseCanWin($userLesson, $user, $exerciseWithNoAnswer->id, $previousExercise->id);
        $this->assertPreviousExerciseCanNotWin($userLesson, $user, $previousExercise->id);
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

        $userLesson = $this->createUserLesson($user, $lesson, $isBidirectional = false);
        $this->assertExerciseCanWin($userLesson, $user, $exerciseWithAnswer1->id);
        $this->assertExerciseCanWin($userLesson, $user, $exerciseWithAnswer2->id);
        $this->assertExerciseCanWin($userLesson, $user, $exerciseWithNoAnswer1->id);
        $this->assertExerciseCanWin($userLesson, $user, $exerciseWithNoAnswer2->id);
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

        $userLesson = $this->createUserLesson($user, $lesson, $isBidirectional = false);
        $this->assertExerciseCanWin($userLesson, $user, $exerciseWithAnswer1->id, $previousExercise->id);
        $this->assertExerciseCanWin($userLesson, $user, $exerciseWithAnswer2->id, $previousExercise->id);
        $this->assertExerciseCanWin($userLesson, $user, $exerciseWithNoAnswer1->id, $previousExercise->id);
        $this->assertExerciseCanWin($userLesson, $user, $exerciseWithNoAnswer2->id, $previousExercise->id);
        $this->assertPreviousExerciseCanNotWin($userLesson, $user, $previousExercise->id);
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

        $userLesson = $this->createUserLesson($user, $lesson, $isBidirectional = false);
        $this->assertExerciseCanWin($userLesson, $user, $exerciseWithAnswer->id);
        $this->assertExerciseCanWin($userLesson, $user, $exerciseWithNoAnswer->id);
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

        $userLesson = $this->createUserLesson($user, $lesson, $isBidirectional = false);
        $this->assertExerciseCanWin($userLesson, $user, $exerciseWithAnswer->id);
        $this->assertExerciseCanWin($userLesson, $user, $exerciseWithNoAnswer->id);
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

        $userLesson = $this->createUserLesson($user, $lesson, $isBidirectional = false);
        $this->assertExerciseCanWin($userLesson, $user, $exerciseWithAnswer->id);
        $this->assertExerciseCanWin($userLesson, $user, $exerciseWithNoAnswer->id);
    }

    /** @test */
    public function itShould_notReturnRandomExercise_lessonHasNoExercises()
    {
        $lesson = $this->createLesson();

        $userLesson = $this->createUserLesson($this->createUser(), $lesson, $isBidirectional = false);
        $result = $this->learningService->fetchRandomExerciseOfLesson($userLesson, $this->createUser());

        $this->assertNull($result);
    }

    /** @test */
    public function itShould_notReturnRandomExercise_lessonOnlyHasPreviousExercise()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $previousExercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $userLesson = $this->createUserLesson($user, $lesson, $isBidirectional = false);
        $result = $this->learningService->fetchRandomExerciseOfLesson($userLesson, $user, $previousExercise->id);

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
        $userLesson = $this->createUserLesson($user, $lesson, $isBidirectional = true);
        do {
            if (!$counter--) {
                $this->fail('Bidirectional flag does not work - unable to fetch reverted exercise.');
            }
            $result = $this->learningService->fetchRandomExerciseOfLesson($userLesson, $user);
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
        $userLesson = $this->createUserLesson($user, $lesson, $isBidirectional = false);
        do {
            if (!$counter--) {
                $this->fail('Bidirectional flag does not work - reverted exercise was fetched while it should not.');
            }
            $result = $this->learningService->fetchRandomExerciseOfLesson($userLesson, $user);
            // just to avoid "This test did not perform any assertions" warning
            $this->assertTrue(true);
        } while ($result->answer == $exercise->question);
    }

    private function assertExerciseCanWin(UserLesson $userLesson, User $user, int $exerciseId, int $previousId = null)
    {
        // try 1000 times - it does not bother us if this number is high,
        // as exercise is likely to be returned in one of first attempts anyway
        // keeping this counter big will prevent from occasional false positives
        $counter = 1000;
        do {
            if (!$counter--) {
                $this->fail('Unable to fetch random exercise.');
            }
            $result = $this->learningService->fetchRandomExerciseOfLesson($userLesson, $user, $previousId);
            $this->assertInstanceOf(UserExercise::class, $result);
        } while ($result->exercise_id != $exerciseId);
    }

    private function assertPreviousExerciseCanNotWin(UserLesson $userLesson, User $user, int $previousId)
    {
        // try 5 times - should be enough to often find a failing case
        // it won't be 100% accurate, but we don't want this check to be too slow as well
        $counter = 5;
        do {
            if (!$counter--) {
                // all good - previous exercise was never returned
                return;
            }
            $result = $this->learningService->fetchRandomExerciseOfLesson($userLesson, $user, $previousId);
            $this->assertTrue($result->exercise_id != $previousId, 'Previous exercise was returned');
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
        $user = $this->createUser();
        $exercise = $this->createExercise();
        $this->createExerciseResult([
            'user_id' => $user->id,
            'exercise_id' => $exercise->id,
            'percent_of_good_answers' => $percentOfGoodAnswers,
            // do not leave these two empty, to avoid a '100' returned for no answers at all
            'number_of_good_answers' => 1,
            'number_of_bad_answers' => 1,
        ]);
        $userExercise = $this->createUserExercise($user, $exercise);

        $result = $this->learningService->calculatePoints($userExercise);

        $this->assertEquals($expectedPoints, $result);
    }

    /** @test */
    public function itShould_calculatePoints_goodAndBadAnswersToday_goodMostRecent()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();
        $this->createExerciseResult([
            'user_id' => $user->id,
            'exercise_id' => $exercise->id,
            'percent_of_good_answers' => 50,
            'latest_good_answer' => Carbon::today()->addMinute(),
            'latest_bad_answer' => Carbon::today(),
            // do not leave these two empty, to avoid a '100' returned for no answers at all
            'number_of_good_answers' => 1,
            'number_of_bad_answers' => 1,
        ]);
        $userExercise = $this->createUserExercise($user, $exercise);

        $result = $this->learningService->calculatePoints($userExercise);

        $this->assertEquals(0, $result);
    }

    /** @test */
    public function itShould_calculatePoints_goodAnswerToday_noBadAnswer()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();
        $this->createExerciseResult([
            'user_id' => $user->id,
            'exercise_id' => $exercise->id,
            'percent_of_good_answers' => 50,
            'latest_good_answer' => Carbon::today(),
            // do not leave these two empty, to avoid a '100' returned for no answers at all
            'number_of_good_answers' => 1,
            'number_of_bad_answers' => 1,
        ]);
        $userExercise = $this->createUserExercise($user, $exercise);

        $result = $this->learningService->calculatePoints($userExercise);

        $this->assertEquals(0, $result);
    }

    /** @test */
    public function itShould_calculatePoints_goodAnswerToday_badAnswerYesterday()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();
        $this->createExerciseResult([
            'user_id' => $user->id,
            'exercise_id' => $exercise->id,
            'percent_of_good_answers' => 50,
            'latest_good_answer' => Carbon::today(),
            'latest_bad_answer' => Carbon::yesterday(),
            // do not leave these two empty, to avoid a '100' returned for no answers at all
            'number_of_good_answers' => 1,
            'number_of_bad_answers' => 1,
        ]);
        $userExercise = $this->createUserExercise($user, $exercise);

        $result = $this->learningService->calculatePoints($userExercise);

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
    public function itShould_calculatePoints_badAnswersToday_noGoodAnswer(int $numberOfBadAnswersToday, int $points)
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();
        $this->createExerciseResult([
            'user_id' => $user->id,
            'exercise_id' => $exercise->id,
            'percent_of_good_answers' => 50,
            'latest_bad_answer' => Carbon::today(),
            'number_of_bad_answers_today' => $numberOfBadAnswersToday,
            // do not leave these two empty, to avoid a '100' returned for no answers at all
            'number_of_good_answers' => 1,
            'number_of_bad_answers' => 1,
        ]);
        $userExercise = $this->createUserExercise($user, $exercise);

        $result = $this->learningService->calculatePoints($userExercise);

        $this->assertEquals($points, $result);
    }

    /**
     * @test
     * @dataProvider justBadAnswersTodayProvider
     * @param int $numberOfBadAnswersToday
     * @param int $points
     * @throws \Exception
     */
    public function itShould_calculatePoints_badAnswersToday_goodAnswerYesterday(int $numberOfBadAnswersToday, int $points)
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();
        $this->createExerciseResult([
            'user_id' => $user->id,
            'exercise_id' => $exercise->id,
            'percent_of_good_answers' => 50,
            'latest_bad_answer' => Carbon::today(),
            'latest_good_answer' => Carbon::yesterday(),
            'number_of_bad_answers_today' => $numberOfBadAnswersToday,
            // do not leave these two empty, to avoid a '100' returned for no answers at all
            'number_of_good_answers' => 1,
            'number_of_bad_answers' => 1,
        ]);
        $userExercise = $this->createUserExercise($user, $exercise);

        $result = $this->learningService->calculatePoints($userExercise);

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
        $this->createExerciseResult([
            'user_id' => $user->id,
            'exercise_id' => $exercise->id,
            'number_of_good_answers' => $numberOfGoodAnswers,
            'number_of_bad_answers' => 0,
        ]);
        $userExercise = $this->createUserExercise($user, $exercise);

        $result = $this->learningService->calculatePoints($userExercise);

        $this->assertEquals($points, $result);
    }

    /** @test */
    public function itShould_calculatePoints_noResult()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();
        $userExercise = $this->createUserExercise($user, $exercise);

        $result = $this->learningService->calculatePoints($userExercise);

        $this->assertEquals(100, $result);
    }
}
