<?php

namespace Tests\Http\Controllers\Web;

class LearnControllerTest extends BaseTestCase
{
    // learn

    public function testItShould_showLessonLearnPage()
    {
        $this->be($user = $this->createUser());
        $lesson = $this->createPublicLesson();

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
        $lesson = $this->createPrivateLesson();

        $this->call('GET', '/lessons/' . $lesson->id . '/learn');

        $this->assertForbidden();
    }

    public function testItShould_notShowLessonLearnPage_lessonNotFound()
    {
        $this->be($user = $this->createUser());

        $this->call('GET', '/lessons/-1/learn');

        $this->assertNotFound();
    }
}
