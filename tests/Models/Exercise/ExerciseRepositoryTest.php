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

    public function testItShould_authorizeCreateExercise_userIsLessonOwner()
    {
        $lesson = $this->createLesson();
        $user = $lesson->owner;

        $this->assertTrue($this->repository->authorizeCreateExercise($user->id, $lesson->id));
    }

    public function testItShould_notAuthorizeCreateExercise_userIsNotLessonOwner()
    {
        $lesson = $this->createLesson();
        $user = $this->createUser();

        $this->assertFalse($this->repository->authorizeCreateExercise($user->id, $lesson->id));
    }

    public function testItShould_notAuthorizeCreateExercise_usersDoesNotExist()
    {
        $lesson = $this->createLesson();

        $this->assertFalse($this->repository->authorizeCreateExercise(-1, $lesson->id));
    }

    public function testItShould_notAuthorizeCreateExercise_lessonDoesNotExist()
    {
        $user = $this->createUser();

        $this->assertFalse($this->repository->authorizeCreateExercise($user->id, -1));
    }

    public function testItShould_authorizeFetchExerciseById_userIsLessonOwner()
    {
        $exercise = $this->createExercise();
        $user = $exercise->lesson->owner;

        $this->assertTrue($this->repository->authorizeFetchExerciseById($user->id, $exercise->id));
    }

    public function testItShould_authorizeFetchExerciseById_userSubscribesLesson()
    {
        $exercise = $this->createExercise();
        $user = $this->createUser();
        $user->subscribedLessons()->attach($exercise->lesson);

        $this->assertTrue($this->repository->authorizeFetchExerciseById($user->id, $exercise->id));
    }

    public function testItShould_notAuthorizeFetchExerciseById_userIsNotLessonOwnerAndDoesNotSubscribeIt()
    {
        $exercise = $this->createExercise();
        $user = $this->createUser();

        $this->assertFalse($this->repository->authorizeFetchExerciseById($user->id, $exercise->id));
    }

    public function testItShould_notAuthorizeFetchExerciseById_usersDoesNotExist()
    {
        $exercise = $this->createExercise();

        $this->assertFalse($this->repository->authorizeFetchExerciseById(-1, $exercise->id));
    }

    public function testItShould_notAuthorizeFetchExerciseById_exerciseDoesNotExist()
    {
        $user = $this->createUser();

        $this->assertFalse($this->repository->authorizeFetchExerciseById($user->id, -1));
    }

    public function testItShould_authorizeFetchExercisesOfLesson_userIsLessonOwner()
    {
        $lesson = $this->createLesson();
        $user = $lesson->owner;

        $this->assertTrue($this->repository->authorizeFetchExercisesOfLesson($user->id, $lesson->id));
    }

    public function testItShould_authorizeFetchExercisesOfLesson_userSubscribesLesson()
    {
        $lesson = $this->createLesson();
        $user = $this->createUser();
        $user->subscribedLessons()->attach($lesson);

        $this->assertTrue($this->repository->authorizeFetchExercisesOfLesson($user->id, $lesson->id));
    }

    public function testItShould_notAuthorizeFetchExercisesOfLesson_userIsNotLessonOwnerAndDoesNotSubscribeIt()
    {
        $lesson = $this->createLesson();
        $user = $this->createUser();

        $this->assertFalse($this->repository->authorizeFetchExercisesOfLesson($user->id, $lesson->id));
    }

    public function testItShould_notAuthorizeFetchExercisesOfLesson_usersDoesNotExist()
    {
        $lesson = $this->createLesson();

        $this->assertFalse($this->repository->authorizeFetchExercisesOfLesson(-1, $lesson->id));
    }

    public function testItShould_notAuthorizeFetchExercisesOfLesson_lessonDoesNotExist()
    {
        $user = $this->createUser();

        $this->assertFalse($this->repository->authorizeFetchExercisesOfLesson($user->id, -1));
    }

    public function testItShould_authorizeUpdateExercise_userIsLessonOwner()
    {
        $exercise = $this->createExercise();
        $user = $exercise->lesson->owner;

        $this->assertTrue($this->repository->authorizeUpdateExercise($user->id, $exercise->id));
    }

    public function testItShould_notAuthorizeUpdateExercise_userIsNotLessonOwner()
    {
        $exercise = $this->createExercise();
        $user = $this->createUser();

        $this->assertFalse($this->repository->authorizeUpdateExercise($user->id, $exercise->id));
    }

    public function testItShould_notAuthorizeUpdateExercise_usersDoesNotExist()
    {
        $exercise = $this->createExercise();

        $this->assertFalse($this->repository->authorizeUpdateExercise(-1, $exercise->id));
    }

    public function testItShould_notAuthorizeUpdateExercise_exerciseDoesNotExist()
    {
        $user = $this->createUser();

        $this->assertFalse($this->repository->authorizeUpdateExercise($user->id, -1));
    }

    public function testItShould_authorizeDeleteExercise_userIsLessonOwner()
    {
        $exercise = $this->createExercise();
        $user = $exercise->lesson->owner;

        $this->assertTrue($this->repository->authorizeDeleteExercise($user->id, $exercise->id));
    }

    public function testItShould_notAuthorizeDeleteExercise_userIsNotLessonOwner()
    {
        $exercise = $this->createExercise();
        $user = $this->createUser();

        $this->assertFalse($this->repository->authorizeDeleteExercise($user->id, $exercise->id));
    }

    public function testItShould_notAuthorizeDeleteExercise_usersDoesNotExist()
    {
        $exercise = $this->createExercise();

        $this->assertFalse($this->repository->authorizeDeleteExercise(-1, $exercise->id));
    }

    public function testItShould_notAuthorizeDeleteExercise_exerciseDoesNotExist()
    {
        $user = $this->createUser();

        $this->assertFalse($this->repository->authorizeDeleteExercise($user->id, -1));
    }
}
