<?php

namespace Tests\Services;

use App\Services\LearningService;
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

    /** @test */
    public function itShould_returnRandomExercise_exerciseOnlyHasAnswersOfAnotherUser()
    {
        $lesson = $this->createLesson();
        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->createExerciseResult([
            'user_id' => $this->createUser()->id,
            'exercise_id' => $exercise->id,
            'percent_of_good_answers' => 100,
        ]);

        $this->assertExerciseCanWin($lesson, $this->createUser()->id, $exercise->id);
    }

    /** @test */
    public function itShould_returnRandomExercise_onePossibleExercise()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $exercise = $this->createExercise(['lesson_id' => $lesson->id]);

        $this->assertExerciseCanWin($lesson, $user->id, $exercise->id);
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
            'percent_of_good_answers' => 100,
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
            'percent_of_good_answers' => 100,
        ]);

        $exerciseWithNoAnswer = $this->createExercise(['lesson_id' => $lesson->id]);
        $previousExercise = $this->createExercise();

        $this->assertExerciseCanWin($lesson, $user->id, $exerciseWithAnswer->id, $previousExercise->id);
        $this->assertExerciseCanWin($lesson, $user->id, $exerciseWithNoAnswer->id, $previousExercise->id);
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
        // 1000 retries is more than enough
        $counter = 1000;
        do {
            if (!$counter--) {
                $this->fail('Unable to fetch random exercise.');
            }
            $result = $this->learningService->fetchRandomExerciseOfLesson($lesson, $userId, $previousId);
            $this->assertInstanceOf(Exercise::class, $result);
        } while ($result->id != $exerciseId && $result->id && $previousId);
    }
}
