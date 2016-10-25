<?php

namespace Tests\Http\Controllers\Web;

use App\Models\Lesson\Lesson;

class LessonControllerTest extends BaseTestCase
{
    public function testItShould_showLessonViewPage()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);

        $this->call('GET', '/lessons/' . $lesson->id);

        $this->assertResponseOk();
        $this->assertViewHas('lesson');
    }

    public function testItShould_notShowLessonViewPage_unauthorized()
    {
        $lesson = $this->createLesson();

        $this->call('GET', '/lessons/' . $lesson->id);

        $this->assertUnauthorized();
    }

    public function testItShould_notShowLessonViewPage_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createLesson();

        $this->call('GET', '/lessons/' . $lesson->id);

        $this->assertForbidden();
    }

    public function testItShould_notShowLessonViewPage_lessonNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('GET', '/lessons/-1');

        $this->assertNotFound();
    }

    public function testItShould_showLessonLearnPage()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);

        $this->call('GET', '/lessons/' . $lesson->id . '/learn');

        $this->assertResponseOk();
        $this->assertViewHas('lesson');
    }

    public function testItShould_notShowLessonLearnPage_unauthorized()
    {
        $lesson = $this->createLesson();

        $this->call('GET', '/lessons/' . $lesson->id . '/learn');

        $this->assertUnauthorized();
    }

    public function testItShould_notShowLessonLearnPage_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createLesson();

        $this->call('GET', '/lessons/' . $lesson->id . '/learn');

        $this->assertForbidden();
    }

    public function testItShould_notShowLessonLearnPage_lessonNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('GET', '/lessons/-1/learn');

        $this->assertNotFound();
    }

    public function testItShould_showLessonCreatePage()
    {
        $this->be($user = $this->createUser());

        $this->call('GET', '/lessons/create');

        $this->assertResponseOk();
    }

    public function testItShould_notShowLessonCreatePage_unauthorized()
    {
        $this->call('GET', '/lessons/create');

        $this->assertUnauthorized();
    }

    public function testItShould_showLessonEditPage()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson($user);

        $this->call('GET', '/lessons/' . $lesson->id . '/edit');

        $this->assertResponseOk();
        $this->assertViewHas('lesson');
    }

    public function testItShould_notShowLessonEditPage_unauthorized()
    {
        $lesson = $this->createLesson();

        $this->call('GET', '/lessons/' . $lesson->id . '/edit');

        $this->assertUnauthorized();
    }

    public function testItShould_notShowLessonEditPage_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createLesson();

        $this->call('GET', '/lessons/' . $lesson->id . '/edit');

        $this->assertForbidden();
    }

    public function testItShould_notShowLessonEditPage_lessonNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('GET', '/lessons/-1/edit');

        $this->assertNotFound();
    }

    public function testItShould_subscribeLesson()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson();

        $this->call('POST', '/lessons/' . $lesson->id . '/subscribe');

        $this->assertRedirectedTo('/home');
    }

    public function testItShould_notSubscribeLesson_unauthorized()
    {
        $lesson = $this->createPublicLesson();

        $this->call('POST', '/lessons/' . $lesson->id . '/subscribe');

        $this->assertUnauthorized();
    }

    public function testItShould_notSubscribeLesson_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPrivateLesson();

        $this->call('POST', '/lessons/' . $lesson->id . '/subscribe');

        $this->assertForbidden();
    }

    public function testItShould_notSubscribeLesson_lessonNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('POST', '/lessons/-1/subscribe');

        $this->assertNotFound();
    }

    public function testItShould_unsubscribeLesson()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson();
        $user->subscribedLessons()->save($lesson);

        $this->call('POST', '/lessons/' . $lesson->id . '/unsubscribe');

        $this->assertRedirectedTo('/home');
    }

    public function testItShould_notUnsubscribeLesson_unauthorized()
    {
        $lesson = $this->createPublicLesson();

        $this->call('POST', '/lessons/' . $lesson->id . '/unsubscribe');

        $this->assertUnauthorized();
    }

    public function testItShould_notUnsubscribeLesson_forbidden()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson();

        $this->call('POST', '/lessons/' . $lesson->id . '/unsubscribe');

        $this->assertForbidden();
    }

    public function testItShould_notUnsubscribeLesson_lessonNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('POST', '/lessons/-1/unsubscribe');

        $this->assertNotFound();
    }

    public function testItShould_storeLesson()
    {
        $this->be($user = $this->createUser());

        $input = [
            'visibility' => 'public',
            'name' => uniqid(),
        ];

        $this->call('POST', '/lessons', $input);

        /** @var Lesson $lesson */
        $lesson = $this->last(Lesson::class);

        $this->assertEquals($input['name'], $lesson->name);
        $this->assertEquals($input['visibility'], $lesson->visibility);
        $this->assertRedirectedTo('/lessons/' . $lesson->id);
    }

    public function testItShould_notStoreLesson_unauthorized()
    {
        $input = [
            'visibility' => 'public',
            'name' => uniqid(),
        ];

        $this->call('POST', '/lessons', $input);

        $this->assertUnauthorized();
    }

    public function testItShould_notStoreLesson_invalidInput()
    {
        $this->be($user = $this->createUser());

        $this->call('POST', '/lessons');

        $this->assertInvalidInput();
    }
}
