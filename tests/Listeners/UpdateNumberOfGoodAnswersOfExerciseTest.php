<?php

namespace Tests\Listeners;

use App\Events\ExerciseBadAnswer;
use App\Events\ExerciseGoodAnswer;
use App\Listeners\UpdateNumberOfBadAnswersOfExercise;
use App\Listeners\UpdateNumberOfGoodAnswersOfExercise;
use Carbon\Carbon;

class UpdateNumberOfGoodAnswersOfExerciseTest extends \TestCase
{
    /** @test */
    public function itShould_updateNumberOfGoodAnswersOfExercise()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);
        $exercise = $this->createExercise(['lesson_id' => $lesson]);

        $this->assertEquals(0, $exercise->numberOfBadAnswersOfUser($user->id));

        $listener = new UpdateNumberOfGoodAnswersOfExercise();
        $event = new ExerciseGoodAnswer($exercise->id, $user);

        /*
         * Day 1
         */

        Carbon::setTestNow($now = Carbon::now()->subDays(2));

        // day 1 good answer 1

        $listener->handle($event);

        $exercise = $exercise->fresh();
        $this->assertEquals($now->toDateTimeString(), $exercise->results[0]->latest_good_answer->toDateTimeString());
        $this->assertEquals(1, $exercise->results[0]->number_of_good_answers);
        $this->assertEquals(1, $exercise->results[0]->number_of_good_answers_today);
        $this->assertEquals(null, $exercise->results[0]->latest_bad_answer);
        $this->assertEquals(0, $exercise->results[0]->number_of_bad_answers);
        $this->assertEquals(0, $exercise->results[0]->number_of_bad_answers_today);

        // day 1 good answer 2

        $listener->handle($event);

        $exercise = $exercise->fresh();
        $this->assertEquals($now->toDateTimeString(), $exercise->results[0]->latest_good_answer->toDateTimeString());
        $this->assertEquals(2, $exercise->results[0]->number_of_good_answers);
        $this->assertEquals(2, $exercise->results[0]->number_of_good_answers_today);
        $this->assertEquals(null, $exercise->results[0]->latest_bad_answer);
        $this->assertEquals(0, $exercise->results[0]->number_of_bad_answers);
        $this->assertEquals(0, $exercise->results[0]->number_of_bad_answers_today);

        /*
         * Day 2
         */

        Carbon::setTestNow($now = Carbon::now()->subDays(1));

        // day 2 good answer 1

        $listener->handle($event);

        $exercise = $exercise->fresh();
        $this->assertEquals($now->toDateTimeString(), $exercise->results[0]->latest_good_answer->toDateTimeString());
        $this->assertEquals(3, $exercise->results[0]->number_of_good_answers);
        $this->assertEquals(1, $exercise->results[0]->number_of_good_answers_today);
        $this->assertEquals(null, $exercise->results[0]->latest_bad_answer);
        $this->assertEquals(0, $exercise->results[0]->number_of_bad_answers);
        $this->assertEquals(0, $exercise->results[0]->number_of_bad_answers_today);

        // day 2 good answer 2

        $listener->handle($event);

        $exercise = $exercise->fresh();
        $this->assertEquals($now->toDateTimeString(), $exercise->results[0]->latest_good_answer->toDateTimeString());
        $this->assertEquals(4, $exercise->results[0]->number_of_good_answers);
        $this->assertEquals(2, $exercise->results[0]->number_of_good_answers_today);
        $this->assertEquals(null, $exercise->results[0]->latest_bad_answer);
        $this->assertEquals(0, $exercise->results[0]->number_of_bad_answers);
        $this->assertEquals(0, $exercise->results[0]->number_of_bad_answers_today);
    }

    /** @test */
    public function itShould_updateNumberOfGoodAnswersOfExercise_badWasAddedBefore()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);
        $exercise = $this->createExercise(['lesson_id' => $lesson]);

        Carbon::setTestNow($now = Carbon::now());

        $listener = new UpdateNumberOfBadAnswersOfExercise();
        $event = new ExerciseBadAnswer($exercise->id, $user);
        $listener->handle($event);

        $listener = new UpdateNumberOfGoodAnswersOfExercise();
        $event = new ExerciseGoodAnswer($exercise->id, $user);
        $listener->handle($event);

        $this->assertEquals($now->toDateTimeString(), $exercise->results[0]->latest_good_answer->toDateTimeString());
        $this->assertEquals(1, $exercise->results[0]->number_of_good_answers);
        $this->assertEquals(1, $exercise->results[0]->number_of_good_answers_today);
        $this->assertEquals($now->toDateTimeString(), $exercise->results[0]->latest_bad_answer->toDateTimeString());
        $this->assertEquals(1, $exercise->results[0]->number_of_bad_answers);
        $this->assertEquals(1, $exercise->results[0]->number_of_bad_answers_today);
    }
}
