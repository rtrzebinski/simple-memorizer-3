<?php

namespace Tests\Http\Controllers\Web;

use App\Models\Lesson;

class MainControllerTest extends BaseTestCase
{
    /** @test */
    public function itShould_displayMainPage_authenticatedUser()
    {
        $this->be($user = $this->createUser());

        $this->call('GET', '/');

        $this->assertResponseRedirectedTo('/home');
    }

    /** @test */
    public function itShould_displayMainPage_notAuthenticatedUser()
    {
        $publicLesson = $this->createPublicLesson();
        $this->createPrivateLesson();

        $this->call('GET', '/');

        $this->assertResponseOk();
        $this->assertViewHas('ownedLessons', []);
        $this->assertViewHas('subscribedLessons', []);
        $this->assertCount(1, $this->view()->availableLessons);
        $this->assertInstanceOf(Lesson::class, $this->view()->availableLessons[0]);
        $this->assertEquals($publicLesson->id, $this->view()->availableLessons[0]->id);
        $this->assertViewHas('userHasOwnedOrSubscribedLessons', false);
    }
}
