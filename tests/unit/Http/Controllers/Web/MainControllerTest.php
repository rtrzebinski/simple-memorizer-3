<?php

namespace Tests\Unit\Http\Controllers\Web;

use App\Structures\UserLesson\UserLesson;

class MainControllerTest extends TestCase
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
        $this->createExercisesRequiredToLearnLesson($publicLesson->id);
        $this->createPrivateLesson();

        $this->call('GET', '/');

        $this->assertResponseOk();
        $this->assertViewHas('ownedLessons', []);
        $this->assertViewHas('subscribedLessons', []);
        $this->assertCount(1, $this->view()->availableLessons);
        $this->assertInstanceOf(UserLesson::class, $this->view()->availableLessons[0]);
        $this->assertEquals($publicLesson->id, $this->view()->availableLessons[0]->lesson_id);
        $this->assertViewHas('userHasOwnedOrSubscribedLessons', false);
    }
}
