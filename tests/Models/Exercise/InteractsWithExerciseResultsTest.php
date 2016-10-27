<?php

namespace Tests\Models\Exercise;

use App\Models\Exercise\Exercise;
use App\Models\Exercise\ExerciseRepository;
use App\Models\User\User;
use PHPUnit_Framework_MockObject_MockObject;
use TestCase;

class InteractsWithExerciseResultsTest extends TestCase
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var Exercise
     */
    private $exercise;

    /**
     * @var ExerciseRepository|PHPUnit_Framework_MockObject_MockObject
     */
    private $exerciseRepository;

    public function setUp()
    {
        parent::setUp();
        $this->user = $this->createUser();
        $this->exercise = $this->createExercise();
        $this->exerciseRepository = $this->createMock(ExerciseRepository::class);
        $this->app->instance(ExerciseRepository::class, $this->exerciseRepository);
    }

    public function test_numberOfGoodAnswersOfUser()
    {
        $result = rand(1, 100);
        $this->exerciseRepository->method('numberOfGoodAnswersOfUser')->with($this->exercise->id,
            $this->user->id)->willReturn($result);
        $this->assertEquals($result, $this->exercise->numberOfGoodAnswersOfUser($this->user->id));
    }

    public function test_numberOfBadAnswersOfUser()
    {
        $result = rand(1, 100);
        $this->exerciseRepository->method('numberOfBadAnswersOfUser')->with($this->exercise->id,
            $this->user->id)->willReturn($result);
        $this->assertEquals($result, $this->exercise->numberOfBadAnswersOfUser($this->user->id));
    }

    public function test_percentOfGoodAnswersOfUser()
    {
        $result = rand(1, 100);
        $this->exerciseRepository->method('percentOfGoodAnswersOfUser')->with($this->exercise->id,
            $this->user->id)->willReturn($result);
        $this->assertEquals($result, $this->exercise->percentOfGoodAnswersOfUser($this->user->id));
    }

    public function test_handleGoodAnswer()
    {
        $this->exerciseRepository->expects($this->once())->method('handleGoodAnswer')
            ->with($this->exercise->id, $this->user->id);
        $this->exercise->handleGoodAnswer($this->user->id);
    }

    public function test_handleBadAnswer()
    {
        $this->exerciseRepository->expects($this->once())->method('handleBadAnswer')
            ->with($this->exercise->id, $this->user->id);
        $this->exercise->handleBadAnswer($this->user->id);
    }
}
