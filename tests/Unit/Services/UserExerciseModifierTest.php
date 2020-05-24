<?php

namespace Tests\Unit\Services;

use App\Services\UserExerciseModifier;
use App\Structures\UserExercise\UserExercise;
use InvalidArgumentException;
use TestCase;

class UserExerciseModifierTest extends TestCase
{
    /** @test */
    public function itShould_swapUserExerciseQuestionWithAnswer_100percentProbability()
    {
        $userExercise = $this->createUserExercise($this->createUser(), $this->createExercise());

        $service = new UserExerciseModifier();

        $result = $service->swapQuestionWithAnswer($userExercise, $probability = 100);

        $this->assertInstanceOf(UserExercise::class, $result);
        $this->assertEquals($userExercise->answer, $result->question);
        $this->assertEquals($userExercise->question, $result->answer);
    }

    /** @test */
    public function itShould_swapUserExerciseQuestionWithAnswer_50percentProbability()
    {
        $userExercise = $this->createUserExercise($this->createUser(), $this->createExercise());

        $service = new UserExerciseModifier();

        $result = $service->swapQuestionWithAnswer($userExercise, $probability = 50);

        $this->assertInstanceOf(UserExercise::class, $result);
    }

    /** @test */
    public function itShould_swapUserExerciseQuestionWithAnswer_0percentProbability()
    {
        $userExercise = $this->createUserExercise($this->createUser(), $this->createExercise());

        $service = new UserExerciseModifier();

        $result = $service->swapQuestionWithAnswer($userExercise, $probability = 0);

        $this->assertInstanceOf(UserExercise::class, $result);
        $this->assertEquals($userExercise->question, $result->question);
        $this->assertEquals($userExercise->answer, $result->answer);
    }

    public function invalidProbabilityDataProvider()
    {
        return [
            [-1],
            [101],
        ];
    }

    /**
     * @test
     * @dataProvider  invalidProbabilityDataProvider
     * @param int $probability
     */
    public function itShould_swapUserExerciseQuestionWithAnswer_invalidProbability(int $probability)
    {
        $userExercise = $this->createUserExercise($this->createUser(), $this->createExercise());

        $service = new UserExerciseModifier();

        $this->expectException(InvalidArgumentException::class);

        $service->swapQuestionWithAnswer($userExercise, $probability);
    }
}
