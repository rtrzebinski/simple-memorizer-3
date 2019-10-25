<?php

namespace Tests\Listeners;

use App\Events\ExerciseGoodAnswer;
use App\Listeners\UpdateNumberOfGoodAnswersOfExercise;
use Carbon\Carbon;

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
        $event = new ExerciseGoodAnswer($exercise, $user);

        Carbon::setTestNow($now = Carbon::now()->subDays(2));
        $listener->handle($event);

        $this->assertEquals(1, $exercise->numberOfGoodAnswersOfUser($user->id));
        $this->assertEquals($now, $exercise->results[0]->latest_good_answer);
        $this->assertEquals(null, $exercise->results[0]->latest_bad_answer);

        Carbon::setTestNow($now = Carbon::now()->subDays(1));
        $listener->handle($event);

        $this->assertEquals(2, $exercise->numberOfGoodAnswersOfUser($user->id));
        $this->assertEquals($now, $exercise->fresh()->results[0]->latest_good_answer);
        $this->assertEquals(null, $exercise->fresh()->results[0]->latest_bad_answer);
    }
}
