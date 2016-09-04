<?php

namespace Tests\Http\Controllers\Api;

use Illuminate\Http\Response;
use TestCase;

class LessonControllerTest extends TestCase
{
    public function testItShould_createLesson()
    {
        $user = $this->createUser();

        $input = [
            'visibility' => 'public',
            'name' => uniqid(),
        ];

        $this->callApi('POST', 'lessons', $input, $user);

        $this->assertResponseStatus(Response::HTTP_OK);

        $this->seeJson([
            'visibility' => $input['visibility'],
            'name' => $input['name'],
            'owner_id' => $user->id,
        ]);
    }

    public function testItShould_notCreateLesson_invalidInput_unauthorized()
    {
        $this->callApi('POST', 'lessons');

        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testItShould_notCreateLesson_invalidInput()
    {
        $user = $this->createUser();

        $this->callApi('POST', 'lessons', $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testItShould_subscribeLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $this->callApi('POST', 'lessons/' . $lesson->id . '/user', $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_OK);
        $this->assertEquals($user->id, $lesson->subscribers[0]->id);
    }

    public function testItShould_notSubscribeLesson_unauthorized()
    {
        $lesson = $this->createLesson();

        $this->callApi('POST', 'lessons/' . $lesson->id . '/user');

        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testItShould_notSubscribeLesson_forbidden()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['visibility' => 'private']);

        $this->callApi('POST', 'lessons/' . $lesson->id . '/user', $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
        $this->assertEmpty($lesson->subscribers);
    }

    public function testItShould_notSubscribeLesson_notFound()
    {
        $user = $this->createUser();

        $this->callApi('POST', 'lessons/-1/user', $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }

    public function testItShould_unsubscribeLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        $this->callApi('DELETE', 'lessons/' . $lesson->id . '/user', $input = [], $user);

        $this->assertResponseOk();
        $this->assertCount(0, $lesson->subscribers);
    }

    public function testItShould_notUnsubscribeLesson_unauthorized()
    {
        $lesson = $this->createLesson();

        $this->callApi('DELETE', 'lessons/' . $lesson->id . '/user');

        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testItShould_notUnsubscribeLesson_forbidden()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $this->callApi('DELETE', 'lessons/' . $lesson->id . '/user', $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_notUnsubscribeLesson_notFound()
    {
        $user = $this->createUser();

        $this->callApi('DELETE', 'lessons/-1/user', $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }

    public function testItShould_updateLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);

        $input = [
            'visibility' => 'public',
            'name' => uniqid(),
        ];

        $this->callApi('PATCH', 'lessons/' . $lesson->id, $input, $user);

        $this->assertResponseStatus(Response::HTTP_OK);
        
        $this->seeJson([
            'visibility' => $input['visibility'],
            'name' => $input['name'],
        ]);
    }

    public function testItShould_notUpdateLesson_unauthorized()
    {
        $lesson = $this->createLesson();

        $this->callApi('PATCH', 'lessons/' . $lesson->id);

        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testItShould_notUpdateLesson_invalidInput()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);

        $input = [
            'visibility' => uniqid(),
        ];

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

        $this->callApi('PATCH', 'lessons/' . $lesson->id, $input, $user);

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_notUpdateLesson_notFound()
    {
        $user = $this->createUser();

        $this->callApi('PATCH', 'lessons/-1', $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }

    public function testItShould_fetchOwnedLessons()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);

        $this->callApi('GET', 'lessons/owned', $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_OK);
        $this->seeJson([$lesson->toArray()]);
    }

    public function testItShould_fetchSubscribedLessons()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        $this->callApi('GET', 'lessons/subscribed', $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_OK);
        $this->seeJsonSubset([$lesson->toArray()]);
    }

    public function testItShould_deleteLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);

        $this->callApi('DELETE', 'lessons/' . $lesson->id, $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_OK);
        $this->assertNull($lesson->fresh());
    }

    public function testItShould_notDeleteLesson_unauthorized()
    {
        $lesson = $this->createLesson();

        $this->callApi('DELETE', 'lessons/' . $lesson->id);

        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testItShould_notDeleteLesson_forbidden()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $this->callApi('DELETE', 'lessons/' . $lesson->id, $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    public function testItShould_notDeleteLesson_notFound()
    {
        $user = $this->createUser();

        $this->callApi('DELETE', 'lessons/-1', $input = [], $user);

        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }
}
