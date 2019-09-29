<?php

namespace Tests\Listeners;

use App\Events\GoodAnswer;
use App\Listeners\UpdateNumberOfGoodAnswersOfExercise;

class UpdateNumberOfGoodAnswersOfExerciseTest extends \TestCase
{
    /** @test */
    public function itShould_updateNumberOfBadAnswersOfExercise()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);
        $exercise = $this->createExercise(['lesson_id' => $lesson]);

        $this->assertEquals(0, $exercise->numberOfBadAnswersOfUser($user->id));

        $listener = new UpdateNumberOfGoodAnswersOfExercise();
        $event = new GoodAnswer($exercise, $user->id);
        $listener->handle($event);

        $this->assertEquals(1, $exercise->numberOfGoodAnswersOfUser($user->id));

        $listener->handle($event);

        $this->assertEquals(2, $exercise->numberOfGoodAnswersOfUser($user->id));
    }
}
