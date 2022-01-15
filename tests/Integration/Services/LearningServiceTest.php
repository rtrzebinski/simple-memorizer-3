<?php

namespace Tests\Integration\Services;

use App\Models\User;
use App\Services\LearningService;
use App\Services\ArrayRandomizer;
use App\Services\PointsCalculator;
use App\Structures\UserExercise\AuthenticatedUserExerciseRepository;
use App\Structures\UserExercise\UserExercise;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use TestCase;

class LearningServiceTest extends TestCase
{
    // fetchRandomExerciseOfLesson

    /** @test */
    public function itShould_returnRandomExercise_onePossibleExercise_noExerciseResult()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $userExercises = collect(
            [
                $this->createUserExercise($user, $exercise),
            ]
        );

        $this->assertExerciseCanWin($userExercises, $user, $exercise->id);
    }

    /** @test */
    public function itShould_returnRandomExercise_onePossibleExercise_withExerciseResult()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->createExerciseResult(
            [
                'user_id' => $user->id,
                'exercise_id' => $exercise->id,
                'percent_of_good_answers' => 10,
            ]
        );

        $userExercises = collect(
            [
                $this->createUserExercise($user, $exercise),
            ]
        );

        $this->assertExerciseCanWin($userExercises, $user, $exercise->id);
    }

    /** @test */
    public function itShould_returnRandomExercise_onePossibleExercise_withExerciseResultOfAnotherUser()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);
        $lesson->subscribe($user);

        $this->createExerciseResult(
            [
                'user_id' => $this->createUser()->id,
                'exercise_id' => $exercise->id,
                'percent_of_good_answers' => 10,
            ]
        );

        $userExercises = collect(
            [
                $this->createUserExercise($user, $exercise),
            ]
        );

        $this->assertExerciseCanWin($userExercises, $user, $exercise->id);
    }

    /** @test */
    public function itShould_returnRandomExercise_onePossibleExercise_withPrevious()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        $exerciseWithAnswer = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->createExerciseResult(
            [
                'user_id' => $this->createUser()->id,
                'exercise_id' => $exerciseWithAnswer->id,
                'percent_of_good_answers' => 10,
            ]
        );

        $previousExercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $userExercises = collect(
            [
                $this->createUserExercise($user, $exerciseWithAnswer),
                $this->createUserExercise($user, $previousExercise),
            ]
        );

        $this->assertExerciseCanWin($userExercises, $user, $exerciseWithAnswer->id, $previousExercise->id);
        $this->assertExerciseCanWin($userExercises, $user, $previousExercise->id, $exerciseWithAnswer->id);
    }

    /** @test */
    public function itShould_returnRandomExercise_oneExcludedExerciseAndExcludedPrevious()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        // exercises is excluded
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);
        $this->createExerciseResult(
            [
                'user_id' => $user->id,
                'exercise_id' => $exercise->id,
                'percent_of_good_answers' => 10,
                'number_of_good_answers' => 1,
                'number_of_good_answers_today' => 1,
                'latest_good_answer' => Carbon::today(),
            ]
        );

        // why excluded:
        // user had just good answer today
        // return 0 point to not bother user with this question anymore today
        // it makes more sense to serve it another day than serve again today

        // previous is excluded
        $previous = $this->createExercise(['lesson_id' => $lesson->id]);
        $this->createExerciseResult(
            [
                'user_id' => $user->id,
                'exercise_id' => $previous->id,
                'percent_of_good_answers' => 10,
                'number_of_good_answers' => 1,
                'number_of_good_answers_today' => 1,
                'latest_good_answer' => Carbon::today(),
            ]
        );

        $userExercises = collect(
            [
                $this->createUserExercise($user, $exercise),
                $this->createUserExercise($user, $previous),
            ]
        );

        $learningService = new LearningService(
            new AuthenticatedUserExerciseRepository($user),
            new PointsCalculator(),
            new ArrayRandomizer()
        );
        $result = $learningService->findUserExerciseToLearn($userExercises, $previous->id);

        $this->assertNull($result);
    }

    /** @test */
    public function itShould_returnRandomExercise_twoExcluded_withPreviousNotAnswered()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        $exercise1 = $this->createExercise(['lesson_id' => $lesson->id]);
        $this->createExerciseResult(
            [
                'user_id' => $user->id,
                'exercise_id' => $exercise1->id,
                'percent_of_good_answers' => 10,
                'number_of_good_answers' => 1,
                'number_of_good_answers_today' => 1,
                'latest_good_answer' => Carbon::today(),
            ]
        );

        $exercise2 = $this->createExercise(['lesson_id' => $lesson->id]);
        $this->createExerciseResult(
            [
                'user_id' => $user->id,
                'exercise_id' => $exercise2->id,
                'percent_of_good_answers' => 10,
                'number_of_good_answers' => 1,
                'number_of_good_answers_today' => 1,
                'latest_good_answer' => Carbon::today(),
            ]
        );

        // why excluded:
        // user had just good answer today
        // return 0 point to not bother user with this question anymore today
        // it makes more sense to serve it another day than serve again today

        $previousExercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $userExercises = collect(
            [
                $this->createUserExercise($user, $exercise1),
                $this->createUserExercise($user, $exercise2),
                $this->createUserExercise($user, $previousExercise),
            ]
        );

        $learningService = new LearningService(
            new AuthenticatedUserExerciseRepository($user),
            new PointsCalculator(),
            new ArrayRandomizer()
        );
        $result = $learningService->findUserExerciseToLearn($userExercises, $previousExercise->id);

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
        $this->createExerciseResult(
            [
                'user_id' => $user->id,
                'exercise_id' => $exercise1->id,
                'percent_of_good_answers' => 10,
                'number_of_good_answers' => 1,
                'number_of_good_answers_today' => 1,
                'latest_good_answer' => Carbon::today(),
            ]
        );

        $exercise2 = $this->createExercise(['lesson_id' => $lesson->id]);
        $this->createExerciseResult(
            [
                'user_id' => $user->id,
                'exercise_id' => $exercise2->id,
                'percent_of_good_answers' => 10,
                'number_of_good_answers' => 1,
                'number_of_good_answers_today' => 1,
                'latest_good_answer' => Carbon::today(),
            ]
        );

        // why excluded:
        // user had just good answer today
        // return 0 point to not bother user with this question anymore today
        // it makes more sense to serve it another day than serve again today

        $previousExercise = $this->createExercise(['lesson_id' => $lesson->id]);
        $this->createExerciseResult(
            [
                'user_id' => $user->id,
                'exercise_id' => $previousExercise->id,
                'percent_of_good_answers' => 10,
            ]
        );

        $userExercises = collect(
            [
                $this->createUserExercise($user, $exercise1),
                $this->createUserExercise($user, $exercise2),
                $this->createUserExercise($user, $previousExercise),
            ]
        );

        $learningService = new LearningService(
            new AuthenticatedUserExerciseRepository($user),
            new PointsCalculator(),
            new ArrayRandomizer()
        );
        $result = $learningService->findUserExerciseToLearn($userExercises, $previousExercise->id);

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
        $this->createExerciseResult(
            [
                'user_id' => $user->id,
                'exercise_id' => $exercise1->id,
                'percent_of_good_answers' => 10,
                'number_of_good_answers' => 1,
                'number_of_good_answers_today' => 1,
                'latest_good_answer' => Carbon::today(),
            ]
        );

        $exercise2 = $this->createExercise(['lesson_id' => $lesson->id]);
        $this->createExerciseResult(
            [
                'user_id' => $user->id,
                'exercise_id' => $exercise2->id,
                'percent_of_good_answers' => 10,
                'number_of_good_answers' => 1,
                'number_of_good_answers_today' => 1,
                'latest_good_answer' => Carbon::today(),
            ]
        );

        // why excluded:
        // user had just good answer today
        // return 0 point to not bother user with this question anymore today
        // it makes more sense to serve it another day than serve again today

        $previousExercise = $this->createExercise(['lesson_id' => $lesson->id]);
        $this->createExerciseResult(
            [
                'user_id' => $user->id,
                'exercise_id' => $previousExercise->id,
                'percent_of_good_answers' => 10,
                'number_of_good_answers' => 1,
                'number_of_good_answers_today' => 1,
                'latest_good_answer' => Carbon::today(),
            ]
        );

        $userExercises = collect(
            [
                $this->createUserExercise($user, $exercise1),
                $this->createUserExercise($user, $exercise2),
                $this->createUserExercise($user, $previousExercise),
            ]
        );

        $learningService = new LearningService(
            new AuthenticatedUserExerciseRepository($user),
            new PointsCalculator(),
            new ArrayRandomizer()
        );
        $result = $learningService->findUserExerciseToLearn($userExercises, $previousExercise->id);

        $this->assertNull($result);
    }

    /** @test */
    public function itShould_returnRandomExercise_twoPossibleExercises()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        $exerciseWithAnswer = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->createExerciseResult(
            [
                'user_id' => $this->createUser()->id,
                'exercise_id' => $exerciseWithAnswer->id,
                'percent_of_good_answers' => 10,
            ]
        );

        $exerciseWithNoAnswer = $this->createExercise(['lesson_id' => $lesson->id]);

        $userExercises = collect(
            [
                $this->createUserExercise($user, $exerciseWithAnswer),
                $this->createUserExercise($user, $exerciseWithNoAnswer),
            ]
        );

        $this->assertExerciseCanWin($userExercises, $user, $exerciseWithAnswer->id);
        $this->assertExerciseCanWin($userExercises, $user, $exerciseWithNoAnswer->id);
    }

    /** @test */
    public function itShould_returnRandomExercise_twoPossibleExercises_withPrevious()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        $exerciseWithAnswer = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->createExerciseResult(
            [
                'user_id' => $this->createUser()->id,
                'exercise_id' => $exerciseWithAnswer->id,
                'percent_of_good_answers' => 10,
            ]
        );

        $exerciseWithNoAnswer = $this->createExercise(['lesson_id' => $lesson->id]);
        $previousExercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $userExercises = collect(
            [
                $this->createUserExercise($user, $exerciseWithAnswer),
                $this->createUserExercise($user, $exerciseWithNoAnswer),
                $this->createUserExercise($user, $previousExercise),
            ]
        );

        $this->assertExerciseCanWin($userExercises, $user, $exerciseWithAnswer->id, $previousExercise->id);
        $this->assertExerciseCanWin($userExercises, $user, $exerciseWithNoAnswer->id, $previousExercise->id);
        $this->assertPreviousExerciseCanNotWin($userExercises, $user, $previousExercise->id);
    }

    /** @test */
    public function itShould_returnRandomExercise_fourPossibleExercises()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        $exerciseWithAnswer1 = $this->createExercise(['lesson_id' => $lesson->id]);
        $this->createExerciseResult(
            [
                'user_id' => $this->createUser()->id,
                'exercise_id' => $exerciseWithAnswer1->id,
                'percent_of_good_answers' => 10,
            ]
        );

        $exerciseWithAnswer2 = $this->createExercise(['lesson_id' => $lesson->id]);
        $this->createExerciseResult(
            [
                'user_id' => $this->createUser()->id,
                'exercise_id' => $exerciseWithAnswer2->id,
                'percent_of_good_answers' => 10,
            ]
        );

        $exerciseWithNoAnswer1 = $this->createExercise(['lesson_id' => $lesson->id]);

        $exerciseWithNoAnswer2 = $this->createExercise(['lesson_id' => $lesson->id]);

        $userExercises = collect(
            [
                $this->createUserExercise($user, $exerciseWithAnswer1),
                $this->createUserExercise($user, $exerciseWithAnswer2),
                $this->createUserExercise($user, $exerciseWithNoAnswer1),
                $this->createUserExercise($user, $exerciseWithNoAnswer2),
            ]
        );

        $this->assertExerciseCanWin($userExercises, $user, $exerciseWithAnswer1->id);
        $this->assertExerciseCanWin($userExercises, $user, $exerciseWithAnswer2->id);
        $this->assertExerciseCanWin($userExercises, $user, $exerciseWithNoAnswer1->id);
        $this->assertExerciseCanWin($userExercises, $user, $exerciseWithNoAnswer2->id);
    }

    /** @test */
    public function itShould_returnRandomExercise_fourPossibleExercises_withPrevious()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        $exerciseWithAnswer1 = $this->createExercise(['lesson_id' => $lesson->id]);
        $this->createExerciseResult(
            [
                'user_id' => $this->createUser()->id,
                'exercise_id' => $exerciseWithAnswer1->id,
                'percent_of_good_answers' => 10,
            ]
        );

        $exerciseWithAnswer2 = $this->createExercise(['lesson_id' => $lesson->id]);
        $this->createExerciseResult(
            [
                'user_id' => $this->createUser()->id,
                'exercise_id' => $exerciseWithAnswer2->id,
                'percent_of_good_answers' => 10,
            ]
        );

        $exerciseWithNoAnswer1 = $this->createExercise(['lesson_id' => $lesson->id]);

        $exerciseWithNoAnswer2 = $this->createExercise(['lesson_id' => $lesson->id]);

        $previousExercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $userExercises = collect(
            [
                $this->createUserExercise($user, $exerciseWithAnswer1),
                $this->createUserExercise($user, $exerciseWithAnswer2),
                $this->createUserExercise($user, $exerciseWithNoAnswer1),
                $this->createUserExercise($user, $exerciseWithNoAnswer2),
                $this->createUserExercise($user, $previousExercise),
            ]
        );

        $this->assertExerciseCanWin($userExercises, $user, $exerciseWithAnswer1->id, $previousExercise->id);
        $this->assertExerciseCanWin($userExercises, $user, $exerciseWithAnswer2->id, $previousExercise->id);
        $this->assertExerciseCanWin($userExercises, $user, $exerciseWithNoAnswer1->id, $previousExercise->id);
        $this->assertExerciseCanWin($userExercises, $user, $exerciseWithNoAnswer2->id, $previousExercise->id);
        $this->assertPreviousExerciseCanNotWin($userExercises, $user, $previousExercise->id);
    }

    /** @test */
    public function itShould_returnRandomExercise_goodAndBadAnswerToday()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        $exerciseWithAnswer = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->createExerciseResult(
            [
                'user_id' => $this->createUser()->id,
                'exercise_id' => $exerciseWithAnswer->id,
                'percent_of_good_answers' => 10,
                'latest_good_answer' => Carbon::now(),
                'latest_bad_answer' => Carbon::now(),
            ]
        );

        $exerciseWithNoAnswer = $this->createExercise(['lesson_id' => $lesson->id]);

        $userExercises = collect(
            [
                $this->createUserExercise($user, $exerciseWithAnswer),
                $this->createUserExercise($user, $exerciseWithNoAnswer),
            ]
        );

        $this->assertExerciseCanWin($userExercises, $user, $exerciseWithAnswer->id);
        $this->assertExerciseCanWin($userExercises, $user, $exerciseWithNoAnswer->id);
    }

    /** @test */
    public function itShould_returnRandomExercise_goodAnswerToday()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        $exerciseWithAnswer = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->createExerciseResult(
            [
                'user_id' => $this->createUser()->id,
                'exercise_id' => $exerciseWithAnswer->id,
                'percent_of_good_answers' => 10,
                'latest_good_answer' => Carbon::now(),
            ]
        );

        $exerciseWithNoAnswer = $this->createExercise(['lesson_id' => $lesson->id]);

        $userExercises = collect(
            [
                $this->createUserExercise($user, $exerciseWithAnswer),
                $this->createUserExercise($user, $exerciseWithNoAnswer),
            ]
        );

        $this->assertExerciseCanWin($userExercises, $user, $exerciseWithAnswer->id);
        $this->assertExerciseCanWin($userExercises, $user, $exerciseWithNoAnswer->id);
    }

    /** @test */
    public function itShould_returnRandomExercise_badAnswerToday()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        $exerciseWithAnswer = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->createExerciseResult(
            [
                'user_id' => $this->createUser()->id,
                'exercise_id' => $exerciseWithAnswer->id,
                'percent_of_good_answers' => 10,
                'latest_bad_answer' => Carbon::now(),
            ]
        );

        $exerciseWithNoAnswer = $this->createExercise(['lesson_id' => $lesson->id]);

        $userExercises = collect(
            [
                $this->createUserExercise($user, $exerciseWithAnswer),
                $this->createUserExercise($user, $exerciseWithNoAnswer),
            ]
        );

        $this->assertExerciseCanWin($userExercises, $user, $exerciseWithAnswer->id);
        $this->assertExerciseCanWin($userExercises, $user, $exerciseWithNoAnswer->id);
    }

    /** @test */
    public function itShould_notReturnRandomExercise_noExercises()
    {
        $user = $this->createUser();

        $learningService = new LearningService(
            new AuthenticatedUserExerciseRepository($user),
            new PointsCalculator(),
            new ArrayRandomizer()
        );
        $result = $learningService->findUserExerciseToLearn(collect([]));

        $this->assertNull($result);
    }

    /** @test */
    public function itShould_notReturnRandomExercise_onlyPreviousExercise()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $previousExercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $userExercises = collect(
            [
                $this->createUserExercise($user, $previousExercise),
            ]
        );

        $learningService = new LearningService(
            new AuthenticatedUserExerciseRepository($user),
            new PointsCalculator(),
            new ArrayRandomizer()
        );
        $result = $learningService->findUserExerciseToLearn($userExercises, $previousExercise->id);

        $this->assertNull($result);
    }

    private function assertExerciseCanWin(
        Collection $userExercises,
        User $user,
        int $exerciseId,
        int $previousId = null
    ) {
        // try 1000 times - it does not bother us if this number is high,
        // as exercise is likely to be returned in one of first attempts anyway
        // keeping this counter big will prevent from occasional false positives
        $counter = 1000;
        do {
            if (!$counter--) {
                $this->fail('Unable to fetch random exercise.');
            }
            $learningService = new LearningService(
                new AuthenticatedUserExerciseRepository($user),
                new PointsCalculator(),
                new ArrayRandomizer()
            );
            $result = $learningService->findUserExerciseToLearn($userExercises, $previousId);
            $this->assertInstanceOf(UserExercise::class, $result);
        } while ($result->exercise_id != $exerciseId);
    }

    private function assertPreviousExerciseCanNotWin(Collection $userExercises, User $user, int $previousId)
    {
        // try 5 times - should be enough to often find a failing case
        // it won't be 100% accurate, but we don't want this check to be too slow as well
        $counter = 5;
        do {
            if (!$counter--) {
                // all good - previous exercise was never returned
                return;
            }
            $learningService = new LearningService(
                new AuthenticatedUserExerciseRepository($user),
                new PointsCalculator(),
                new ArrayRandomizer()
            );
            $result = $learningService->findUserExerciseToLearn($userExercises, $previousId);
            $this->assertTrue($result->exercise_id != $previousId, 'Previous exercise was returned');
        } while (1);
    }
}
