<?php

namespace Tests\Models\Lesson;

use App\Models\Exercise\ExerciseRepository;
use App\Models\User\User;
use PHPUnit_Framework_MockObject_MockObject;
use TestCase;
use App\Models\Lesson\Lesson;

class InteractsWithExercisesTest extends TestCase
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var Lesson
     */
    private $lesson;

    /**
     * @var ExerciseRepository|PHPUnit_Framework_MockObject_MockObject
     */
    private $exerciseRepository;

    public function setUp()
    {
        parent::setUp();
        $this->user = $this->createUser();
        $this->lesson = $this->createLesson();
        $this->exerciseRepository = $this->createMock(ExerciseRepository::class);
        $this->app->instance(ExerciseRepository::class, $this->exerciseRepository);
    }

    public function testItShould_fetchRandomExercise()
    {
        $previousExerciseId = rand(1, 100);
        $exercise = $this->createExercise();

        $this->exerciseRepository->method('fetchRandomExerciseOfLesson')
            ->with($this->lesson, $this->user->id, $previousExerciseId)
            ->willReturn($exercise);

        $this->assertEquals($exercise, $this->lesson->fetchRandomExercise($this->user->id, $previousExerciseId));
    }
}
