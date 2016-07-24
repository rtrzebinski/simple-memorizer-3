<?php

namespace Tests\Http\Controllers\Api;

use App\Models\Exercise\ExerciseRepository;
use Illuminate\Http\Response;
use PHPUnit_Framework_MockObject_MockObject;
use TestCase;

class ExerciseControllerTest extends TestCase
{
    /**
     * @var ExerciseRepository|PHPUnit_Framework_MockObject_MockObject
     */
    private $exerciseRepositoryMock;

    public function setUp()
    {
        parent::setUp();
        $this->exerciseRepositoryMock = $this->getMock(ExerciseRepository::class);
        $this->app->instance(ExerciseRepository::class, $this->exerciseRepositoryMock);
    }

    public function protectedRoutesProvider()
    {
        return [
            ['POST', '/exercises'],
            ['GET', '/exercises'],
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
        $exercise = $this->createExercise();
        $question = uniqid();
        $answer = uniqid();

        $input = [
            'question' => $question,
            'answer' => $answer,
        ];

        $this->exerciseRepositoryMock->method('createExercise')->with($user->id, $input)
            ->willReturn($exercise);

        $this->callApi('POST', '/exercises', $input, $user);

        $this->assertJsonResponse($exercise, Response::HTTP_CREATED);
    }

    public function testItShould_notCreateExercise_invalidInput()
    {
        $this->callApi('POST', '/exercises', [], $this->createUser());

        $this->exerciseRepositoryMock->expects($this->never())->method('createExercise');

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testItShould_fetchExercisesOfUser()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $this->exerciseRepositoryMock->method('fetchExercisesOfUser')->with($user->id)->willReturn(collect([$exercise]));

        $this->callApi('GET', '/exercises', [], $user);

        $this->assertJsonResponse([$exercise]);
    }

    public function testItShould_fetchExercise()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise(['user_id' => $user->id]);

        $this->exerciseRepositoryMock->method('findExerciseById')->with($exercise->id)->willReturn($exercise);

        $this->callApi('GET', '/exercises/' . $exercise->id, [], $user);

        $this->assertJsonResponse($exercise);
    }

    public function testItShould_notFetchExercise_exerciseDoesNotExist()
    {
        $user = $this->createUser();

        $this->exerciseRepositoryMock->expects($this->never())->method('findExerciseById');

        $this->callApi('GET', '/exercises/' . -1, [], $user);

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_notFetchExercise_exerciseDoesNotBelongToUser()
    {
        $user = $this->createUser();

        $this->exerciseRepositoryMock->expects($this->never())->method('findExerciseById');

        $this->callApi('GET', '/exercises/' . $this->createExercise()->id, [], $user);

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_updateExercise()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise(['user_id' => $user->id]);
        $question = uniqid();
        $answer = uniqid();

        $input = [
            'question' => $question,
            'answer' => $answer,
        ];

        $this->exerciseRepositoryMock->method('updateExercise')->with($exercise->id, $input)->willReturn($exercise);

        $this->callApi('PATCH', '/exercises/' . $exercise->id, $input, $user);

        $this->assertJsonResponse($exercise);
    }

    public function testItShould_notUpdateExercise_exerciseDoesNotExist()
    {
        $user = $this->createUser();

        $this->exerciseRepositoryMock->expects($this->never())->method('updateExercise');

        $this->callApi('PATCH', '/exercises/' . -1, [
            'question' => uniqid(),
            'answer' => uniqid(),
        ], $user);

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_notUpdateExercise_invalidInput()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise(['user_id' => $user->id]);

        $this->exerciseRepositoryMock->expects($this->never())->method('updateExercise');

        $this->callApi('PATCH', '/exercises/' . $exercise->id, [], $user);

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testItShould_notUpdateExercise_exerciseDoesNotBelongToUser()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();
        $question = uniqid();
        $answer = uniqid();

        $input = [
            'question' => $question,
            'answer' => $answer,
        ];

        $this->exerciseRepositoryMock->method('findExerciseById')
            ->with($exercise->id)->willReturn($exercise);
        $this->exerciseRepositoryMock->method('updateExercise')->with($exercise->id, $input)->willReturn($exercise);

        $this->callApi('PATCH', '/exercises/' . $exercise->id, $input, $user);

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_deleteExercise()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise(['user_id' => $user->id]);

        $this->exerciseRepositoryMock->expects($this->once())->method('deleteExercise')->with($exercise->id);

        $this->callApi('DELETE', '/exercises/' . $exercise->id, [], $user);

        $this->assertResponseStatus(Response::HTTP_NO_CONTENT);
    }

    public function testItShould_notDeleteExercise_exerciseDoesNotBelongToUser()
    {
        $user = $this->createUser();
        $exercise = $this->createExercise();

        $this->exerciseRepositoryMock->expects($this->never())->method('deleteExercise');

        $this->callApi('DELETE', '/exercises/' . $exercise->id, [], $user);

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }
}
