<?php

namespace Tests\Http\Controllers\Api;

use App\Models\Lesson;

class LessonControllerTest extends BaseTestCase
{
    // storeLesson

    /** @test */
    public function itShould_storeLesson()
    {
        $user = $this->createUser();

        $input = [
            'visibility' => 'public',
            'name' => uniqid(),
        ];

        $this->callApi('POST', '/lessons', $input, $user);

        $this->assertResponseOk();

        $this->seeJsonFragment([
            'visibility' => $input['visibility'],
            'name' => $input['name'],
            'owner_id' => $user->id,
        ]);

        /** @var Lesson $lesson */
        $lesson = $this->last(Lesson::class);
        $this->assertEquals($input['visibility'], $lesson->visibility);
        $this->assertEquals($input['name'], $lesson->name);
        $this->assertEquals($user->id, $lesson->owner_id);

        // ensure user and a lesson have a row in pivot table,
        // but it should not be considered a regular subscriber
        $this->assertDatabaseHas('lesson_user', [
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'percent_of_good_answers' => 0,
        ]);
        $this->assertCount(1, $lesson->subscribedUsers);
        $this->assertCount(0, $lesson->subscribedUsersWithOwnerExcluded);
    }

    /** @test */
    public function itShould_notStoreLesson_unauthorized()
    {
        $this->callApi('POST', '/lessons');

        $this->assertResponseUnauthorised();
    }

    /** @test */
    public function itShould_notStoreLesson_invalidInput()
    {
        $user = $this->createUser();

        $this->callApi('POST', '/lessons', $input = [], $user);

        $this->assertResponseInvalidInput();
    }

    // subscribeLesson

    /** @test */
    public function itShould_subscribeLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $this->callApi('POST', '/lessons/'.$lesson->id.'/user', $input = [], $user);

        $this->assertResponseOk();
        $this->assertEquals($user->id, $lesson->subscribedUsers[0]->id);
    }

    /** @test */
    public function itShould_notSubscribeLesson_unauthorized()
    {
        $lesson = $this->createLesson();

        $this->callApi('POST', '/lessons/'.$lesson->id.'/user');

        $this->assertResponseUnauthorised();
    }

    /** @test */
    public function itShould_notSubscribeLesson_forbidden()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['visibility' => 'private']);

        $this->callApi('POST', '/lessons/'.$lesson->id.'/user', $input = [], $user);

        $this->assertResponseForbidden();
        $this->assertEmpty($lesson->subscribedUsers);
    }

    /** @test */
    public function itShould_notSubscribeLesson_notFound()
    {
        $user = $this->createUser();

        $this->callApi('POST', '/lessons/-1/user', $input = [], $user);

        $this->assertResponseNotFound();
    }

    // unsubscribeLesson

    /** @test */
    public function itShould_unsubscribeLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        $this->callApi('DELETE', '/lessons/'.$lesson->id.'/user', $input = [], $user);

        $this->assertResponseOk();
        $this->assertCount(0, $lesson->subscribedUsers);
    }

    /** @test */
    public function itShould_notUnsubscribeLesson_unauthorized()
    {
        $lesson = $this->createLesson();

        $this->callApi('DELETE', '/lessons/'.$lesson->id.'/user');

        $this->assertResponseUnauthorised();
    }

    /** @test */
    public function itShould_notUnsubscribeLesson_forbidden()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $this->callApi('DELETE', '/lessons/'.$lesson->id.'/user', $input = [], $user);

        $this->assertResponseForbidden();
    }

    /** @test */
    public function itShould_notUnsubscribeLesson_notFound()
    {
        $user = $this->createUser();

        $this->callApi('DELETE', '/lessons/-1/user', $input = [], $user);

        $this->assertResponseNotFound();
    }

    // fetchLesson

    /** @test */
    public function itShould_fetchLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createPublicLesson();

        $this->callApi('GET', '/lessons/'.$lesson->id, $input = [], $user);

        $this->assertResponseOk();
        $this->seeJsonFragment($lesson->toArray());
    }

    /** @test */
    public function itShould_notFetchLesson_unauthorized()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);

        $this->callApi('GET', '/lessons/'.$lesson->id);

        $this->assertResponseUnauthorised();
    }

    /** @test */
    public function itShould_notFetchLesson_forbidden()
    {
        $user = $this->createUser();
        $lesson = $this->createPrivateLesson();

        $this->callApi('GET', '/lessons/'.$lesson->id, $input = [], $user);

        $this->assertResponseForbidden();
    }

    /** @test */
    public function itShould_notFetchLesson_notFound()
    {
        $user = $this->createUser();

        $this->callApi('GET', '/lessons/-1', $input = [], $user);

        $this->assertResponseNotFound();
    }

    // fetchOwnedLessons

    /** @test */
    public function itShould_fetchOwnedLessons()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);

        $this->callApi('GET', '/lessons/owned', $input = [], $user);

        $this->assertResponseOk();
        $this->seeJson([$lesson->toArray()]);
    }

    /** @test */
    public function itShould_notFetchOwnedLessons_unauthorized()
    {
        $this->callApi('GET', '/lessons/owned');

        $this->assertResponseUnauthorised();
    }

    // fetchSubscribedLessons

    /** @test */
    public function itShould_fetchSubscribedLessons()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();
        $lesson->subscribe($user);

        $this->callApi('GET', '/lessons/subscribed', $input = [], $user);

        $this->assertResponseOk();
        $this->seeJson([$lesson->toArray()]);
    }

    /** @test */
    public function itShould_notFetchSubscribedLessons_unauthorized()
    {
        $this->callApi('GET', '/lessons/subscribed');

        $this->assertResponseUnauthorised();
    }

    // updateLesson

    /** @test */
    public function itShould_updateLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);

        $input = [
            'visibility' => 'public',
            'name' => uniqid(),
        ];

        $this->callApi('PATCH', '/lessons/'.$lesson->id, $input, $user);

        $this->assertResponseOk();

        $this->seeJsonFragment([
            'visibility' => $input['visibility'],
            'name' => $input['name'],
        ]);

        /** @var Lesson $lesson */
        $lesson = $lesson->fresh();
        $this->assertEquals($input['visibility'], $lesson->visibility);
        $this->assertEquals($input['name'], $lesson->name);
    }

    /** @test */
    public function itShould_notUpdateLesson_unauthorized()
    {
        $lesson = $this->createLesson();

        $this->callApi('PATCH', '/lessons/'.$lesson->id);

        $this->assertResponseUnauthorised();
    }

    /** @test */
    public function itShould_notUpdateLesson_invalidInput()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);

        $input = [
            'visibility' => uniqid(),
        ];

        $this->callApi('PATCH', '/lessons/'.$lesson->id, $input, $user);

        $this->assertResponseInvalidInput();
    }

    /** @test */
    public function itShould_notUpdateLesson_forbidden()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $input = [
            'visibility' => 'public',
            'name' => uniqid(),
        ];

        $this->callApi('PATCH', '/lessons/'.$lesson->id, $input, $user);

        $this->assertResponseForbidden();
    }

    /** @test */
    public function itShould_notUpdateLesson_notFound()
    {
        $user = $this->createUser();

        $this->callApi('PATCH', '/lessons/-1', $input = [], $user);

        $this->assertResponseNotFound();
    }

    // deleteLesson

    /** @test */
    public function itShould_deleteLesson()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson(['owner_id' => $user->id]);

        $this->callApi('DELETE', '/lessons/'.$lesson->id, $input = [], $user);

        $this->assertResponseOk();
        $this->assertNull($lesson->fresh());
    }

    /** @test */
    public function itShould_notDeleteLesson_unauthorized()
    {
        $lesson = $this->createLesson();

        $this->callApi('DELETE', '/lessons/'.$lesson->id);

        $this->assertResponseUnauthorised();
    }

    /** @test */
    public function itShould_notDeleteLesson_forbidden()
    {
        $user = $this->createUser();
        $lesson = $this->createLesson();

        $this->callApi('DELETE', '/lessons/'.$lesson->id, $input = [], $user);

        $this->assertResponseForbidden();
    }

    /** @test */
    public function itShould_notDeleteLesson_notFound()
    {
        $user = $this->createUser();

        $this->callApi('DELETE', '/lessons/-1', $input = [], $user);

        $this->assertResponseNotFound();
    }
}
