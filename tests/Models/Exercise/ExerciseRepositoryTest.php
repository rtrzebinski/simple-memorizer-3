<?php

namespace Tests\Models\Exercise;

use App\Models\Exercise\Exercise;
use App\Models\Exercise\ExerciseRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use TestCase;

class ExerciseRepositoryTest extends TestCase
{
    /**
     * @var ExerciseRepository
     */
    private $repository;

    public function setUp()
    {
        parent::setUp();
        $this->repository = new ExerciseRepository();
    }

    public function testItShould_createExercise()
    {
        $lesson = $this->createLesson();
        $question = uniqid();
        $answer = uniqid();

        $exercise = $this->repository->createExercise([
            'question' => $question,
            'answer' => $answer,
        ], $lesson->id);

        $this->assertInstanceOf(Exercise::class, $exercise);
        $this->assertEquals($question, $exercise->question);
        $this->assertEquals($answer, $exercise->answer);
        $this->assertEquals($lesson->id, $exercise->lesson_id);
    }

    public function testItShould_findExerciseById()
    {
        $exercise = $this->createExercise();
        $this->assertEquals($exercise->id, $this->repository->findExerciseById($exercise->id)->id);
    }

    public function testItShould_notFindExerciseById_exerciseDoesNotExist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->repository->findExerciseById(-1);
    }

    public function testItShould_fetchExercisesOfLesson()
    {
        $exercise = $this->createExercise();

        $result = $this->repository->fetchExercisesOfLesson($exercise->lesson_id);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(Exercise::class, $result[0]);
        $this->assertEquals($exercise->id, $result[0]->id);
    }

    public function testItShould_updateExercise()
    {
        $exercise = $this->createExercise();
        $question = uniqid();
        $answer = uniqid();

        $exercise = $this->repository->updateExercise([
            'question' => $question,
            'answer' => $answer,
        ], $exercise->id)->fresh();

        $this->assertInstanceOf(Exercise::class, $exercise);
        $this->assertEquals($question, $exercise->question);
        $this->assertEquals($answer, $exercise->answer);
    }

    public function testItShould_notUpdateExercise_exerciseDoesNotExist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->repository->updateExercise([], -1);
    }

    public function testItShould_deleteExercise()
    {
        $exercise = $this->createExercise();

        $this->repository->deleteExercise($exercise->id);

        $this->assertNull($exercise->fresh());
    }

    public function testItShould_notDeleteExercise_exerciseDoesNotExist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->repository->deleteExercise(-1);
    }
}
