<?php

namespace Tests\Http\Controllers\Web;

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
}
