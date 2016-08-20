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
        $this->lessonRepository = $this->getMock(LessonRepositoryInterface::class);
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

        $this->lessonRepository->expects($this->once())->method('createLesson')->with($input,
            $user->id)->willReturn($lesson);

        $this->callApi('POST', 'lessons', $input, $user)->seeJson($lesson->toArray());

        $this->assertResponseStatus(Response::HTTP_CREATED);
    }

    public function testItShould_notCreateLesson_invalidInput()
    {
        $user = $this->createUser();

        $this->lessonRepository->expects($this->never())->method('createLesson');

        $this->callApi('POST', 'lessons', $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testItShould_subscribePublicAndNotOwnedLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson();

        $this->lessonRepository->expects($this->once())->method('subscribeLesson')->with($user->id, $lesson->id);

        $this->callApi('POST', 'lessons/' . $lesson->id . '/user', $input = [], $user);

        $this->assertResponseOk();
    }

    public function testItShould_subscribePublicAndOwnedLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);

        $this->lessonRepository->expects($this->once())->method('subscribeLesson')->with($user->id, $lesson->id);

        $this->callApi('POST', 'lessons/' . $lesson->id . '/user', $input = [], $user);

        $this->assertResponseOk();
    }

    public function testItShould_subscribePrivateAndOwnedLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPrivateLesson($user);

        $this->lessonRepository->expects($this->once())->method('subscribeLesson')->with($user->id, $lesson->id);

        $this->callApi('POST', 'lessons/' . $lesson->id . '/user', $input = [], $user);

        $this->assertResponseOk();
    }

    public function testItShould_notSubscribePrivateAndNotOwnedLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPrivateLesson();

        $this->lessonRepository->expects($this->never())->method('subscribeLesson');

        $this->callApi('POST', 'lessons/' . $lesson->id . '/user', $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_notSubscribeLesson_userAlreadySubscribedPublicAndOwnedLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);
        $user->subscribedLessons()->save($lesson);

        $this->lessonRepository->expects($this->never())->method('subscribeLesson');

        $this->callApi('POST', 'lessons/' . $lesson->id . '/user', $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_notSubscribeLesson_userAlreadySubscribedPublicAndNotOwnedLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson();
        $user->subscribedLessons()->save($lesson);

        $this->lessonRepository->expects($this->never())->method('subscribeLesson');

        $this->callApi('POST', 'lessons/' . $lesson->id . '/user', $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_notSubscribeLesson_userAlreadySubscribedPrivateAndOwnedLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPrivateLesson($user);
        $user->subscribedLessons()->save($lesson);

        $this->lessonRepository->expects($this->never())->method('subscribeLesson');

        $this->callApi('POST', 'lessons/' . $lesson->id . '/user', $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_notSubscribeLesson_lessonDoesNotExist()
    {
        $user = $this->createUser();

        $this->lessonRepository->expects($this->never())->method('subscribeLesson');

        $this->callApi('POST', 'lessons/-1/user', $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_unsubscribeLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson();
        $user->subscribedLessons()->save($lesson);

        $this->lessonRepository->expects($this->once())->method('unsubscribeLesson')->with($user->id, $lesson->id);

        $this->callApi('DELETE', 'lessons/' . $lesson->id . '/user', $input = [], $user);

        $this->assertResponseOk();
    }

    public function testItShould_notUnsubscribeLesson_lessonDoesNotExist()
    {
        $user = $this->createUser();

        $this->lessonRepository->expects($this->never())->method('unsubscribeLesson');

        $this->callApi('DELETE', 'lessons/-1/user', $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_notUnsubscribeLesson_userDoesNotSubscribeLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson();

        $this->lessonRepository->expects($this->never())->method('unsubscribeLesson');

        $this->callApi('DELETE', 'lessons/' . $lesson->id . '/user', $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_updateLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);

        $input = [
            'visibility' => 'public',
            'name' => uniqid(),
        ];

        $this->lessonRepository->expects($this->once())->method('updateLesson')->with($input, $lesson->id)
            ->willReturn($lesson);

        $this->callApi('PATCH', 'lessons/' . $lesson->id, $input, $user);

        $this->assertResponseOk();
    }

    public function testItShould_notUpdateLesson_invalidInput()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);

        $input = [
            'visibility' => uniqid(),
        ];

        $this->lessonRepository->expects($this->never())->method('updateLesson');

        $this->callApi('PATCH', 'lessons/' . $lesson->id, $input, $user);

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testItShould_notUpdateLesson_userIsNotLessonOwner()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson();

        $input = [
            'visibility' => 'public',
            'name' => uniqid(),
        ];

        $this->lessonRepository->expects($this->never())->method('updateLesson');

        $this->callApi('PATCH', 'lessons/' . $lesson->id, $input, $user);

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_fetchOwnedLessons()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $this->lessonRepository->expects($this->once())->method('fetchOwnedLessons')
            ->with($user->id)->willReturn(collect([$lesson]));

        $this->callApi('GET', 'lessons/owned', $input = [], $user)->seeJson([$lesson->toArray()]);

        $this->assertResponseOk();
    }

    public function testItShould_fetchSubscribedLessons()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $this->lessonRepository->expects($this->once())->method('fetchSubscribedLessons')
            ->with($user->id)->willReturn(collect([$lesson]));

        $this->callApi('GET', 'lessons/subscribed', $input = [], $user)->seeJson([$lesson->toArray()]);

        $this->assertResponseOk();
    }

    public function testItShould_deleteLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson($user);

        $this->lessonRepository->expects($this->once())->method('deleteLesson')->with($lesson->id);

        $this->callApi('DELETE', 'lessons/' . $lesson->id, $input = [], $user);

        $this->assertResponseOk();
    }

    public function testItShould_notDeleteLesson_lessonDoesNotExist()
    {
        $user = $this->createUser();

        $this->lessonRepository->expects($this->never())->method('deleteLesson');

        $this->callApi('DELETE', 'lessons/-1', $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_notDeleteLesson_userIsNotLessonOwner()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson();

        $this->lessonRepository->expects($this->never())->method('deleteLesson');

        $this->callApi('DELETE', 'lessons/' . $lesson->id, $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    protected function createPublicLesson(User $user = null) : Lesson
    {
        $attributes = ['visibility' => 'public'];

        if ($user) {
            $attributes['owner_id'] = $user->id;
        }

        return $this->createLesson($attributes);
    }

    protected function createPrivateLesson(User $user = null) : Lesson
    {
        $attributes = ['visibility' => 'private'];

        if ($user) {
            $attributes['owner_id'] = $user->id;
        }

        return $this->createLesson($attributes);
    }
}
