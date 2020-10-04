<?php

namespace Tests\Unit\Services;

use App\Services\LearningService;
use App\Services\ArrayRandomizer;
use App\Services\PointsCalculator;
use App\Structures\UserExercise\AuthenticatedUserExerciseRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use TestCase;

class LearningServiceTest extends TestCase
{
    /**
     * @var AuthenticatedUserExerciseRepositoryInterface|MockObject
     */
    private $userExerciseRepository;

    /**
     * @var ArrayRandomizer|MockObject
     */
    private $randomizationService;

    /**
     * @var PointsCalculator|MockObject
     */
    private $pointsCalculator;

    /**
     * @var LearningService
     */
    private LearningService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->userExerciseRepository = $this->createMock(AuthenticatedUserExerciseRepositoryInterface::class);
        $this->pointsCalculator = $this->createMock(PointsCalculator::class);
        $this->randomizationService = $this->createMock(ArrayRandomizer::class);
        $this->service = new LearningService(
            $this->userExerciseRepository,
            $this->pointsCalculator,
            $this->randomizationService
        );
    }

    // fetchRandomExerciseOfLesson

    /** @test */
    public function itShould_findUserExerciseToLearn_oneExercise_onePoint()
    {
        $user = $this->createUser();
        $userExercise1 = $this->createUserExercise($user, $this->createExercise());
        $userExercises = collect([$userExercise1]);

        $this->pointsCalculator
            ->expects($this->once())
            ->method('calculatePoints')
            ->with($userExercise1)
            ->willReturn(1);

        $this->randomizationService
            ->expects($this->once())
            ->method('randomArrayElement')
            ->with([0])
            ->willReturn(0);

        $result = $this->service->findUserExerciseToLearn($userExercises);

        $this->assertSame($result, $userExercise1);
    }

    /** @test */
    public function itShould_findUserExerciseToLearn_oneExercise_twoPoints()
    {
        $user = $this->createUser();
        $userExercise1 = $this->createUserExercise($user, $this->createExercise());
        $userExercises = collect([$userExercise1]);

        $this->pointsCalculator
            ->expects($this->once())
            ->method('calculatePoints')
            ->with($userExercise1)
            ->willReturn(2);

        $this->randomizationService
            ->expects($this->once())
            ->method('randomArrayElement')
            ->with([0, 0])
            ->willReturn(0);

        $result = $this->service->findUserExerciseToLearn($userExercises);

        $this->assertSame($result, $userExercise1);
    }

    /** @test */
    public function itShould_findUserExerciseToLearn_manyExercises_onePoint()
    {
        $user = $this->createUser();
        $userExercise1 = $this->createUserExercise($user, $this->createExercise());
        $userExercise2 = $this->createUserExercise($user, $this->createExercise());
        $userExercise3 = $this->createUserExercise($user, $this->createExercise());
        $userExercises = collect([$userExercise1, $userExercise2, $userExercise3]);

        $this->pointsCalculator
            ->expects($this->exactly(3))
            ->method('calculatePoints')
            ->withConsecutive([$userExercise1], [$userExercise2], [$userExercise3])
            ->willReturnOnConsecutiveCalls(1, 1, 1);

        $this->randomizationService
            ->expects($this->once())
            ->method('randomArrayElement')
            ->with([0, 1, 2])
            ->willReturn(0);

        $result = $this->service->findUserExerciseToLearn($userExercises);

        $this->assertSame($result, $userExercise1);
    }

    /** @test */
    public function itShould_findUserExerciseToLearn_manyExercises_variousPoints()
    {
        $user = $this->createUser();
        $userExercise1 = $this->createUserExercise($user, $this->createExercise());
        $userExercise2 = $this->createUserExercise($user, $this->createExercise());
        $userExercise3 = $this->createUserExercise($user, $this->createExercise());
        $userExercises = collect([$userExercise1, $userExercise2, $userExercise3]);

        $this->pointsCalculator
            ->expects($this->exactly(3))
            ->method('calculatePoints')
            ->withConsecutive([$userExercise1], [$userExercise2], [$userExercise3])
            ->willReturnOnConsecutiveCalls(1, 2, 3);

        $this->randomizationService
            ->expects($this->once())
            ->method('randomArrayElement')
            ->with([0, 1, 1, 2, 2, 2])
            ->willReturn(0);

        $result = $this->service->findUserExerciseToLearn($userExercises);

        $this->assertSame($result, $userExercise1);
    }

    /** @test */
    public function itShould_findUserExerciseToLearn_excludePreviousExercise()
    {
        $user = $this->createUser();
        $userExercise1 = $this->createUserExercise($user, $this->createExercise());
        $userExercise2 = $this->createUserExercise($user, $this->createExercise());
        $previousUserExercise = $this->createUserExercise($user, $this->createExercise());
        $userExercises = collect([$userExercise1, $userExercise2, $previousUserExercise]);

        $this->pointsCalculator
            ->expects($this->exactly(2))
            ->method('calculatePoints')
            ->withConsecutive([$userExercise1], [$userExercise2])
            ->willReturnOnConsecutiveCalls(1, 1);

        $this->randomizationService
            ->expects($this->once())
            ->method('randomArrayElement')
            ->with([0, 1])
            ->willReturn(0);

        $result = $this->service->findUserExerciseToLearn($userExercises, $previousUserExercise->exercise_id);

        $this->assertSame($result, $userExercise1);
    }

    /** @test */
    public function itShould_findUserExerciseToLearn_emptyInputCollection()
    {
        $userExercises = collect([]);

        $this->pointsCalculator
            ->expects($this->never())
            ->method('calculatePoints');

        $this->randomizationService
            ->expects($this->never())
            ->method('randomArrayElement');

        $result = $this->service->findUserExerciseToLearn($userExercises);

        $this->assertSame($result, null);
    }

    /** @test */
    public function itShould_findUserExerciseToLearn_onlyPreviousExerciseToBeDisplayed()
    {
        $user = $this->createUser();
        $userExercise1 = $this->createUserExercise($user, $this->createExercise());
        $previousUserExercise = $this->createUserExercise($user, $this->createExercise());
        $userExercises = collect([$userExercise1, $previousUserExercise]);

        $this->pointsCalculator
            ->expects($this->exactly(2))
            ->method('calculatePoints')
            ->withConsecutive([$userExercise1], [$previousUserExercise])
            ->willReturnOnConsecutiveCalls(0, 1);

        $this->userExerciseRepository
            ->expects($this->once())
            ->method('fetchUserExerciseOfExercise')
            ->with($previousUserExercise->exercise_id)
            ->willReturn($previousUserExercise);

        $this->randomizationService
            ->expects($this->never())
            ->method('randomArrayElement');

        $result = $this->service->findUserExerciseToLearn($userExercises, $previousUserExercise->exercise_id);

        $this->assertSame($result, $previousUserExercise);
    }
}
