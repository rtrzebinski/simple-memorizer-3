<?php

namespace Tests\Listeners;

use App\Events\ExerciseBadAnswer;
use App\Listeners\UpdateNumberOfBadAnswersOfExercise;

class UpdateNumberOfBadAnswersOfExerciseTest extends \TestCase
{
    /** @test */
    public function itShould_updateNumberOfBadAnswersOfExercise()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);
        $exercise = $this->createExercise(['lesson_id' => $lesson]);

        $this->assertEquals(0, $exercise->numberOfBadAnswersOfUser($user->id));

        $listener = new UpdateNumberOfBadAnswersOfExercise();
        $event = new ExerciseBadAnswer($exercise, $user);
        $listener->handle($event);

        $this->assertEquals(1, $exercise->numberOfBadAnswersOfUser($user->id));

        $listener->handle($event);

        $this->assertEquals(2, $exercise->numberOfBadAnswersOfUser($user->id));
    }
}
