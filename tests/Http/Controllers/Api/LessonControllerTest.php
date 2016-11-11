<?php

namespace Tests\Http\Controllers\Api;

use App\Models\Lesson\Lesson;

class LessonControllerTest extends BaseTestCase
{
    // storeLesson

    public function testItShould_storeLesson()
    {
        $user = $this->createUser();

        $input = [
            'visibility' => 'public',
            'name' => uniqid(),
        ];

        $this->callApi('POST', '/lessons', $input, $user);

        $this->assertResponseOk();

        $this->seeJson([
            'visibility' => $input['visibility'],
            'name' => $input['name'],
            'owner_id' => $user->id,
        ]);

        /** @var Lesson $lesson */
        $lesson = $this->last(Lesson::class);
        $this->assertEquals($input['visibility'], $lesson->visibility);
        $this->assertEquals($input['name'], $lesson->name);
        $this->assertEquals($user->id, $lesson->owner_id);
    }

    public function testItShould_notStoreLesson_unauthorized()
    {
        $this->callApi('POST', '/lessons');

        $this->assertUnauthorised();
    }

    public function testItShould_notStoreLesson_invalidInput()
    {
        $user = $this->createUser();

        $this->callApi('POST', '/lessons', $input = [], $user);

        $this->assertInvalidInput();
    }

    // subscribeLesson

    public function testItShould_subscribeLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $this->callApi('POST', '/lessons/' . $lesson->id . '/user', $input = [], $user);

        $this->assertResponseOk();
        $this->assertEquals($user->id, $lesson->subscribers[0]->id);
    }

    public function testItShould_notSubscribeLesson_unauthorized()
    {
        $lesson = $this->createLesson();

        $this->callApi('POST', '/lessons/' . $lesson->id . '/user');

        $this->assertUnauthorised();
    }

    public function testItShould_notSubscribeLesson_forbidden()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['visibility' => 'private']);

        $this->callApi('POST', '/lessons/' . $lesson->id . '/user', $input = [], $user);

        $this->assertForbidden();
        $this->assertEmpty($lesson->subscribers);
    }

    public function testItShould_notSubscribeLesson_notFound()
    {
        $user = $this->createUser();

        $this->callApi('POST', '/lessons/-1/user', $input = [], $user);

        $this->assertNotFound();
    }

    // unsubscribeLesson

    public function testItShould_unsubscribeLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        $this->callApi('DELETE', '/lessons/' . $lesson->id . '/user', $input = [], $user);

        $this->assertResponseOk();
        $this->assertCount(0, $lesson->subscribers);
    }

    public function testItShould_notUnsubscribeLesson_unauthorized()
    {
        $lesson = $this->createLesson();

        $this->callApi('DELETE', '/lessons/' . $lesson->id . '/user');

        $this->assertUnauthorised();
    }

    public function testItShould_notUnsubscribeLesson_forbidden()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $this->callApi('DELETE', '/lessons/' . $lesson->id . '/user', $input = [], $user);

        $this->assertForbidden();
    }

    public function testItShould_notUnsubscribeLesson_notFound()
    {
        $user = $this->createUser();

        $this->callApi('DELETE', '/lessons/-1/user', $input = [], $user);

        $this->assertNotFound();
    }

    // fetchLesson

    public function testItShould_fetchLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson();

        $this->callApi('GET', '/lessons/' . $lesson->id, $input = [], $user);

        $this->assertResponseOk();
        $this->seeJson($lesson->toArray());
    }

    public function testItShould_notFetchLesson_unauthorized()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);

        $this->callApi('GET', '/lessons/' . $lesson->id);

        $this->assertUnauthorised();
    }

    public function testItShould_notFetchLesson_forbidden()
    {
        $user = $this->createUser();
        $lesson = $this->createPrivateLesson();

        $this->callApi('GET', '/lessons/' . $lesson->id, $input = [], $user);

        $this->assertForbidden();
    }

    public function testItShould_notFetchLesson_notFound()
    {
        $user = $this->createUser();

        $this->callApi('GET', '/lessons/-1', $input = [], $user);

        $this->assertNotFound();
    }

    // fetchOwnedLessons

    public function testItShould_fetchOwnedLessons()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);

        $this->callApi('GET', '/lessons/owned', $input = [], $user);

        $this->assertResponseOk();
        $this->seeJson([$lesson->toArray()]);
    }

    public function testItShould_notFetchOwnedLessons_unauthorized()
    {
        $this->callApi('GET', '/lessons/owned');

        $this->assertUnauthorised();
    }

    // fetchSubscribedLessons

    public function testItShould_fetchSubscribedLessons()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        $this->callApi('GET', '/lessons/subscribed', $input = [], $user);

        $this->assertResponseOk();
        $this->seeJsonSubset([$lesson->toArray()]);
    }

    public function testItShould_notFetchSubscribedLessons_unauthorized()
    {
        $this->callApi('GET', '/lessons/subscribed');

        $this->assertUnauthorised();
    }

    // updateLesson

    public function testItShould_updateLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);

        $input = [
            'visibility' => 'public',
            'name' => uniqid(),
        ];

        $this->callApi('PATCH', '/lessons/' . $lesson->id, $input, $user);

        $this->assertResponseOk();

        $this->seeJson([
            'visibility' => $input['visibility'],
            'name' => $input['name'],
        ]);

        /** @var Lesson $lesson */
        $lesson = $lesson->fresh();
        $this->assertEquals($input['visibility'], $lesson->visibility);
        $this->assertEquals($input['name'], $lesson->name);
    }

    public function testItShould_notUpdateLesson_unauthorized()
    {
        $lesson = $this->createLesson();

        $this->callApi('PATCH', '/lessons/' . $lesson->id);

        $this->assertUnauthorised();
    }

    public function testItShould_notUpdateLesson_invalidInput()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);

        $input = [
            'visibility' => uniqid(),
        ];

        $this->callApi('PATCH', '/lessons/' . $lesson->id, $input, $user);

        $this->assertInvalidInput();
    }

    public function testItShould_notUpdateLesson_forbidden()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $input = [
            'visibility' => 'public',
            'name' => uniqid(),
        ];

        $this->callApi('PATCH', '/lessons/' . $lesson->id, $input, $user);

        $this->assertForbidden();
    }

    public function testItShould_notUpdateLesson_notFound()
    {
        $user = $this->createUser();

        $this->callApi('PATCH', '/lessons/-1', $input = [], $user);

        $this->assertNotFound();
    }

    // deleteLesson

    public function testItShould_deleteLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);

        $this->callApi('DELETE', '/lessons/' . $lesson->id, $input = [], $user);

        $this->assertResponseOk();
        $this->assertNull($lesson->fresh());
    }

    public function testItShould_notDeleteLesson_unauthorized()
    {
        $lesson = $this->createLesson();

        $this->callApi('DELETE', '/lessons/' . $lesson->id);

        $this->assertUnauthorised();
    }

    public function testItShould_notDeleteLesson_forbidden()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $this->callApi('DELETE', '/lessons/' . $lesson->id, $input = [], $user);

        $this->assertForbidden();
    }

    public function testItShould_notDeleteLesson_notFound()
    {
        $user = $this->createUser();

        $this->callApi('DELETE', '/lessons/-1', $input = [], $user);

        $this->assertNotFound();
    }
}
