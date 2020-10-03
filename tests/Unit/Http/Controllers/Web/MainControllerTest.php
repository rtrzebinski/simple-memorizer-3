<?php

namespace Tests\Unit\Http\Controllers\Web;

use App\Structures\UserLesson\UserLesson;
use WebTestCase;

class MainControllerTest extends WebTestCase
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
        $this->assertResponseViewHas('ownedLessons', []);
        $this->assertResponseViewHas('subscribedLessons', []);
        $this->assertCount(1, $this->responseView()->availableLessons);
        $this->assertInstanceOf(UserLesson::class, $this->responseView()->availableLessons[0]);
        $this->assertEquals($publicLesson->id, $this->responseView()->availableLessons[0]->lesson_id);
        $this->assertResponseViewHas('userHasOwnedOrSubscribedLessons', false);
    }
}
