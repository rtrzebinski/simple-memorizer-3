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
        $this->exerciseRepository = $this->createMock(ExerciseRepositoryInterface::class);
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
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $exercise = $this->createExercise();

        $input = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->exerciseRepository
            ->method('authorizeCreateExercise')
            ->with($user->id, $lesson->id)
            ->willReturn(true);

        $this->exerciseRepository
            ->expects($this->once())
            ->method('createExercise')
            ->with($input, $lesson->id)
            ->willReturn($exercise);

        $this->callApi('POST', '/lessons/' . $lesson->id . '/exercises', $input, $user)
            ->seeJson($exercise->toArray())
            ->assertResponseStatus(Response::HTTP_CREATED);
    }

    public function testItShould_notCreateExercise_invalidInput()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $this->exerciseRepository
            ->method('authorizeCreateExercise')
            ->with($user->id, $lesson->id)
            ->willReturn(true);

        $this->exerciseRepository
            ->expects($this->never())
            ->method('createExercise');

        $this->callApi('POST', '/lessons/' . $lesson->id . '/exercises', $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testItShould_notCreateExercise_forbidden()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $input = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->exerciseRepository
            ->method('authorizeCreateExercise')
            ->with($user->id, $lesson->id)
            ->willReturn(false);

        $this->exerciseRepository
            ->expects($this->never())
            ->method('createExercise');

        $this->callApi('POST', '/lessons/' . $lesson->id . '/exercises', $input, $user);

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_fetchExercise()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $this->exerciseRepository
            ->method('authorizeFetchExerciseById')
            ->with($user->id, $exercise->id)
            ->willReturn(true);

        $this->exerciseRepository
            ->expects($this->once())
            ->method('findExerciseById')
            ->with($exercise->id)
            ->willReturn($exercise);

        $this->callApi('GET', '/exercises/' . $exercise->id, $input = [], $user)
            ->seeJson($exercise->toArray());
    }

    public function testItShould_notFetchExercise_forbidden()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $this->exerciseRepository
            ->method('authorizeFetchExerciseById')
            ->with($user->id, $exercise->id)
            ->willReturn(false);

        $this->exerciseRepository
            ->expects($this->never())
            ->method('findExerciseById');

        $this->callApi('GET', '/exercises/' . $exercise->id, $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_fetchExercisesOfLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $exercise = $this->createExercise();

        $this->exerciseRepository
            ->method('authorizeFetchExercisesOfLesson')
            ->with($user->id, $lesson->id)
            ->willReturn(true);

        $this->exerciseRepository
            ->expects($this->once())
            ->method('fetchExercisesOfLesson')
            ->with($lesson->id)
            ->willReturn(collect([$exercise]));

        $this->callApi('GET', '/lessons/' . $lesson->id . '/exercises', $input = [], $user)
            ->seeJson([$exercise->toArray()]);
    }

    public function testItShould_notFetchExercisesOfLesson_forbidden()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $this->exerciseRepository
            ->method('authorizeFetchExercisesOfLesson')
            ->with($user->id, $lesson->id)
            ->willReturn(false);

        $this->exerciseRepository
            ->expects($this->never())
            ->method('fetchExercisesOfLesson');

        $this->callApi('GET', '/lessons/' . $lesson->id . '/exercises', $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_updateExercise()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $input = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->exerciseRepository
            ->method('authorizeUpdateExercise')
            ->with($user->id, $exercise->id)
            ->willReturn(true);

        $this->exerciseRepository
            ->expects($this->once())
            ->method('updateExercise')
            ->with($input, $exercise->id)
            ->willReturn($exercise);

        $this->callApi('PATCH', '/exercises/' . $exercise->id, $input, $user)
            ->seeJson($exercise->toArray());
    }

    public function testItShould_notUpdateExercise_invalidInput()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $this->exerciseRepository
            ->method('authorizeUpdateExercise')
            ->with($user->id, $exercise->id)
            ->willReturn(true);

        $this->exerciseRepository
            ->expects($this->never())
            ->method('updateExercise');

        $this->callApi('PATCH', '/exercises/' . $exercise->id, $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testItShould_notUpdateExercise_forbidden()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $input = [
            'question' => uniqid(),
            'answer' => uniqid(),
        ];

        $this->exerciseRepository
            ->method('authorizeUpdateExercise')
            ->with($user->id, $exercise->id)
            ->willReturn(false);

        $this->exerciseRepository
            ->expects($this->never())
            ->method('updateExercise');

        $this->callApi('PATCH', '/exercises/' . $exercise->id, $input, $user);

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_deleteExercise()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $this->exerciseRepository
            ->method('authorizeDeleteExercise')
            ->with($user->id, $exercise->id)
            ->willReturn(true);

        $this->exerciseRepository
            ->expects($this->once())
            ->method('deleteExercise')
            ->with($exercise->id);

        $this->callApi('DELETE', '/exercises/' . $exercise->id, $input = [], $user);

        $this->assertResponseOk();
    }

    public function testItShould_notDeleteExercise_forbidden()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $this->exerciseRepository
            ->method('authorizeDeleteExercise')
            ->with($user->id, $exercise->id)
            ->willReturn(false);

        $this->exerciseRepository
            ->expects($this->never())
            ->method('deleteExercise');

        $this->callApi('DELETE', '/exercises/' . $exercise->id, $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }
}
