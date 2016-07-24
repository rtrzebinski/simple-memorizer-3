<?php

namespace Tests\Models\Exercise;

use App\Models\Exercise\Exercise;
use App\Models\Exercise\ExerciseRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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

    public function testItShould_fetchExercisesOfUser()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise(['user_id' => $user->id]);

        $result = $this->repository->fetchExercisesOfUser($user->id);

        $this->assertCount(1, $result);
        $this->assertEquals($exercise->id, $result[0]->id);
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

    public function testItShould_createExercise()
    {
        $user = $this->createUser();
        $question = uniqid();
        $answer = uniqid();

        $exercise = $this->repository->createExercise($user->id, [
            'question' => $question,
            'answer' => $answer,
        ]);

        $this->assertInstanceOf(Exercise::class, $exercise);
        $this->assertEquals($question, $exercise->question);
        $this->assertEquals($answer, $exercise->answer);
        $this->assertEquals($user->id, $exercise->user_id);
    }

    public function testItShould_updateExercise()
    {
        $exercise = $this->createExercise();
        $question = uniqid();
        $answer = uniqid();

        $exercise = $this->repository->updateExercise($exercise->id, [
            'question' => $question,
            'answer' => $answer,
        ])->fresh();

        $this->assertInstanceOf(Exercise::class, $exercise);
        $this->assertEquals($question, $exercise->question);
        $this->assertEquals($answer, $exercise->answer);
    }

    public function testItShould_notUpdateExercise_exerciseDoesNotExist()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->repository->updateExercise(-1, []);
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
