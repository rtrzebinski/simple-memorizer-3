<?php

namespace Tests\Models\Exercise;

use TestCase;

class InteractsWithExerciseResultsTest extends TestCase
{
    public function testItShould_defaultNumberOfAnswersToZero()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $this->assertEquals(0, $exercise->numberOfGoodAnswersOfUser($user->id));
        $this->assertEquals(0, $exercise->numberOfBadAnswersOfUser($user->id));
        $this->assertEquals(0, $exercise->percentOfGoodAnswersOfUser($user->id));
    }

    public function testItShould_increaseNumberOfGoodAnswersOfUser()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $exercise->increaseNumberOfGoodAnswersOfUser($user->id);
        $this->assertEquals(1, $exercise->numberOfGoodAnswersOfUser($user->id));
        $this->assertEquals(100, $exercise->percentOfGoodAnswersOfUser($user->id));
    }

    public function testItShould_increaseNumberOfBadAnswersOfUser()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $exercise->increaseNumberOfBadAnswersOfUser($user->id);
        $this->assertEquals(1, $exercise->numberOfBadAnswersOfUser($user->id));
        $this->assertEquals(0, $exercise->percentOfGoodAnswersOfUser($user->id));
    }
}
