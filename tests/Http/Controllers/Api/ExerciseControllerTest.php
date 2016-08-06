<?php

namespace Tests\Http\Controllers\Api;

use App\Models\Exercise\ExerciseRepositoryInterface;
use Illuminate\Http\Response;
use PHPUnit_Framework_MockObject_MockObject;
use TestCase;

class ExerciseControllerTest extends TestCase
{
    /**
     * @var ExerciseRepositoryInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $exerciseRepository;

    public function setUp()
    {
        parent::setUp();
        $this->exerciseRepository = $this->getMock(ExerciseRepositoryInterface::class);
        $this->app->instance(ExerciseRepositoryInterface::class, $this->exerciseRepository);
    }

    public function protectedRoutesProvider()
    {
        return [
            ['POST', '/lessons/1/exercises'],
            ['GET', '/lessons/1/exercises'],
            ['GET', '/exercises/1'],
            ['PATCH', '/exercises/1'],
            ['DELETE', '/exercises/1'],
        ];
    }

    /**
     * @dataProvider protectedRoutesProvider
     * @param string $method
     * @param string $path
     */
    public function testItShould_requireUserAuthentication(string $method, string $path)
    {
        $this->callApi($method, $path);
        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testItShould_createExercise()
    {
        $exercise = $this->createExercise();

        $input = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->exerciseRepository
            ->expects($this->once())
            ->method('createExercise')
            ->with($input, $exercise->lesson->id)
            ->willReturn($exercise);

        $this->callApi('POST', '/lessons/' . $exercise->lesson->id . '/exercises', $input, $exercise->lesson->owner)
            ->seeJson($exercise->toArray())
            ->assertResponseStatus(Response::HTTP_CREATED);
    }

    public function testItShould_notCreateExercise_invalidInput()
    {
        $lesson = $this->createLesson();

        $this->exerciseRepository
            ->expects($this->never())
            ->method('createExercise');

        $this->callApi('POST', '/lessons/' . $lesson->id . '/exercises', $input = [], $lesson->owner);

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testItShould_notCreateExercise_lessonDoesNotExist()
    {
        $input = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->exerciseRepository
            ->expects($this->never())
            ->method('createExercise');

        $this->callApi('POST', '/lessons/-1/exercises', $input, $this->createUser());

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_notCreateExercise_userIsNotLessonOwner()
    {
        $lesson = $this->createLesson();

        $input = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->exerciseRepository
            ->expects($this->never())
            ->method('createExercise');

        $this->callApi('POST', '/lessons/' . $lesson->id . '/exercises', $input, $this->createUser());

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_fetchExercise()
    {
        $exercise = $this->createExercise();

        $this->exerciseRepository
            ->expects($this->once())
            ->method('findExerciseById')
            ->with($exercise->id)
            ->willReturn($exercise);

        $this->callApi('GET', '/exercises/' . $exercise->id, $input = [], $exercise->lesson->owner)
            ->seeJson($exercise->toArray());
    }

    public function testItShould_notFetchExercise_exerciseDoesNotExist()
    {
        $this->exerciseRepository
            ->expects($this->never())
            ->method('findExerciseById');

        $this->callApi('GET', '/exercises/-1', $input = [], $this->createUser());

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_notFetchExercise_userIsNotLessonOwner()
    {
        $exercise = $this->createExercise();

        $this->exerciseRepository
            ->expects($this->never())
            ->method('findExerciseById');

        $this->callApi('GET', '/exercises/' . $exercise->id, $input = [], $this->createUser());

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_fetchExercisesOfLesson()
    {
        $exercise = $this->createExercise();

        $this->exerciseRepository
            ->expects($this->once())
            ->method('fetchExercisesOfLesson')
            ->with($exercise->lesson->id)
            ->willReturn(collect([$exercise]));

        $this->callApi('GET', '/lessons/' . $exercise->lesson->id . '/exercises', $input = [], $exercise->lesson->owner)
            ->seeJson([$exercise->toArray()]);
    }

    public function testItShould_notFetchExercisesOfLesson_lessonDoesNotExist()
    {
        $this->exerciseRepository
            ->expects($this->never())
            ->method('fetchExercisesOfLesson');

        $this->callApi('GET', '/lessons/-1/exercises', $input = [], $this->createUser());

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_notFetchExercisesOfLesson_userIsNotLessonOwner()
    {
        $lesson = $this->createLesson();

        $this->exerciseRepository
            ->expects($this->never())
            ->method('fetchExercisesOfLesson');

        $this->callApi('GET', '/lessons/' . $lesson->id . '/exercises', $input = [], $this->createUser());

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_updateExercise()
    {
        $exercise = $this->createExercise();

        $input = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->exerciseRepository
            ->expects($this->once())
            ->method('updateExercise')
            ->with($input, $exercise->id)
            ->willReturn($exercise);

        $this->callApi('PATCH', '/exercises/' . $exercise->id, $input, $exercise->lesson->owner)
            ->seeJson($exercise->toArray());
    }

    public function testItShould_notUpdateExercise_invalidInput()
    {
        $exercise = $this->createExercise();

        $this->exerciseRepository
            ->expects($this->never())
            ->method('updateExercise');

        $this->callApi('PATCH', '/exercises/' . $exercise->id, $input = [], $exercise->lesson->owner);

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testItShould_notUpdateExercise_exerciseDoesNotExist()
    {
        $input = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->exerciseRepository
            ->expects($this->never())
            ->method('updateExercise');

        $this->callApi('PATCH', '/exercises/-1', $input, $this->createUser());

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_notUpdateExercise_userIsNotLessonOwner()
    {
        $exercise = $this->createExercise();

        $input = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->exerciseRepository
            ->expects($this->never())
            ->method('updateExercise');

        $this->callApi('PATCH', '/exercises/' . $exercise->id, $input, $this->createUser());

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_deleteExercise()
    {
        $exercise = $this->createExercise();

        $this->exerciseRepository
            ->expects($this->once())
            ->method('deleteExercise')
            ->with($exercise->id);

        $this->callApi('DELETE', '/exercises/' . $exercise->id, $input = [], $exercise->lesson->owner);

        $this->assertResponseOk();
    }

    public function testItShould_notDeleteExercise_exerciseDoesNotExist()
    {
        $this->exerciseRepository
            ->expects($this->never())
            ->method('deleteExercise');

        $this->callApi('DELETE', '/exercises/-1', $input = [], $this->createUser());

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_notDeleteExercise_userIsNotLessonOwner()
    {
        $exercise = $this->createExercise();

        $this->exerciseRepository
            ->expects($this->never())
            ->method('deleteExercise');

        $this->callApi('DELETE', '/exercises/' . $exercise->id, $input = [], $this->createUser());

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }
}
