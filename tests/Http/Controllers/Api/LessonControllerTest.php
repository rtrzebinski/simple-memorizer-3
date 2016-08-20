<?php

namespace Tests\Http\Controllers\Api;

use App\Models\Lesson\Lesson;
use App\Models\Lesson\LessonRepositoryInterface;
use App\Models\User\User;
use Illuminate\Http\Response;
use PHPUnit_Framework_MockObject_MockObject;
use TestCase;

class LessonControllerTest extends TestCase
{
    /**
     * @var LessonRepositoryInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $lessonRepository;

    public function setUp()
    {
        parent::setUp();
        $this->lessonRepository = $this->createMock(LessonRepositoryInterface::class);
        $this->app->instance(LessonRepositoryInterface::class, $this->lessonRepository);
    }

    public function testItShould_createLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $input = [
            'visibility' => 'public',
            'name' => uniqid(),
        ];

        $this->lessonRepository
            ->expects($this->once())
            ->method('createLesson')
            ->with($input, $user->id)
            ->willReturn($lesson);

        $this->callApi('POST', 'lessons', $input, $user)->seeJson($lesson->toArray());

        $this->assertResponseStatus(Response::HTTP_CREATED);
    }

    public function testItShould_notCreateLesson_invalidInput()
    {
        $user = $this->createUser();

        $this->lessonRepository
            ->expects($this->never())
            ->method('createLesson');

        $this->callApi('POST', 'lessons', $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testItShould_subscribeLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $this->lessonRepository
            ->method('authorizeSubscribeLesson')
            ->with($user->id, $lesson->id)
            ->willReturn(true);

        $this->lessonRepository
            ->expects($this->once())
            ->method('subscribeLesson')
            ->with($user->id, $lesson->id);

        $this->callApi('POST', 'lessons/' . $lesson->id . '/user', $input = [], $user);

        $this->assertResponseOk();
    }

    public function testItShould_notSubscribeLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $this->lessonRepository
            ->method('authorizeSubscribeLesson')
            ->with($user->id, $lesson->id)
            ->willReturn(false);

        $this->lessonRepository
            ->expects($this->never())
            ->method('subscribeLesson');

        $this->callApi('POST', 'lessons/' . $lesson->id . '/user', $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_unsubscribeLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $this->lessonRepository
            ->method('authorizeUnsubscribeLesson')
            ->with($user->id, $lesson->id)
            ->willReturn(true);

        $this->lessonRepository
            ->expects($this->once())
            ->method('unsubscribeLesson')
            ->with($user->id, $lesson->id);

        $this->callApi('DELETE', 'lessons/' . $lesson->id . '/user', $input = [], $user);

        $this->assertResponseOk();
    }

    public function testItShould_notUnsubscribeLesson_forbidden()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $this->lessonRepository
            ->method('authorizeUnsubscribeLesson')
            ->with($user->id, $lesson->id)
            ->willReturn(false);

        $this->lessonRepository
            ->expects($this->never())
            ->method('unsubscribeLesson');

        $this->callApi('DELETE', 'lessons/' . $lesson->id . '/user', $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_updateLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $input = [
            'visibility' => 'public',
            'name' => uniqid(),
        ];

        $this->lessonRepository
            ->method('authorizeUpdateLesson')
            ->with($user->id, $lesson->id)
            ->willReturn(true);

        $this->lessonRepository
            ->expects($this->once())
            ->method('updateLesson')
            ->with($input, $lesson->id)
            ->willReturn($lesson);

        $this->callApi('PATCH', 'lessons/' . $lesson->id, $input, $user);

        $this->assertResponseOk();
    }

    public function testItShould_notUpdateLesson_invalidInput()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $input = [
            'visibility' => uniqid(),
        ];

        $this->lessonRepository
            ->method('authorizeUpdateLesson')
            ->with($user->id, $lesson->id)
            ->willReturn(true);

        $this->lessonRepository
            ->expects($this->never())
            ->method('updateLesson');

        $this->callApi('PATCH', 'lessons/' . $lesson->id, $input, $user);

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testItShould_notUpdateLesson_forbidden()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $input = [
            'visibility' => 'public',
            'name' => uniqid(),
        ];

        $this->lessonRepository
            ->method('authorizeUpdateLesson')
            ->with($user->id, $lesson->id)
            ->willReturn(false);

        $this->lessonRepository
            ->expects($this->never())
            ->method('updateLesson');

        $this->callApi('PATCH', 'lessons/' . $lesson->id, $input, $user);

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_fetchOwnedLessons()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $this->lessonRepository
            ->expects($this->once())
            ->method('fetchOwnedLessons')
            ->with($user->id)
            ->willReturn(collect([$lesson]));

        $this->callApi('GET', 'lessons/owned', $input = [], $user)->seeJson([$lesson->toArray()]);

        $this->assertResponseOk();
    }

    public function testItShould_fetchSubscribedLessons()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $this->lessonRepository
            ->expects($this->once())
            ->method('fetchSubscribedLessons')
            ->with($user->id)
            ->willReturn(collect([$lesson]));

        $this->callApi('GET', 'lessons/subscribed', $input = [], $user)->seeJson([$lesson->toArray()]);

        $this->assertResponseOk();
    }

    public function testItShould_deleteLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $this->lessonRepository
            ->method('authorizeDeleteLesson')
            ->with($user->id, $lesson->id)
            ->willReturn(true);

        $this->lessonRepository
            ->expects($this->once())
            ->method('deleteLesson')
            ->with($lesson->id);

        $this->callApi('DELETE', 'lessons/' . $lesson->id, $input = [], $user);

        $this->assertResponseOk();
    }

    public function testItShould_notDeleteLesson_forbidden()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $this->lessonRepository
            ->method('authorizeDeleteLesson')
            ->with($user->id, $lesson->id)
            ->willReturn(false);

        $this->lessonRepository
            ->expects($this->never())
            ->method('deleteLesson');

        $this->callApi('DELETE', 'lessons/' . $lesson->id, $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }
}
