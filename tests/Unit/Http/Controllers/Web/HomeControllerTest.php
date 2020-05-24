<?php

namespace Tests\Unit\Http\Controllers\Web;

use WebTestCase;

class HomeControllerTest extends WebTestCase
{
    // index

    /** @test */
    public function itShould_displayHomePage()
    {
        $this->be($user = $this->createUser());
        // availableLessons
        $availableLesson = $this->createLesson();
        $this->createExercisesRequiredToLearnLesson($availableLesson->id);
        // subscribedLessons
        $subscribed = $this->createLesson();
        $subscribed->subscribe($user);
        // ownedLessons
        $this->createPublicLesson($user);
        $this->createPrivateLesson($user);

        $this->call('GET', '/home');

        $this->assertResponseOk();
        $this->assertCount(2, $this->view()->ownedLessons);
        $this->assertCount(1, $this->view()->subscribedLessons);
        $this->assertCount(1, $this->view()->availableLessons);
        $this->assertViewHas('userHasOwnedOrSubscribedLessons', true);
    }

    /** @test */
    public function itShould_displayHomePage_noLessons()
    {
        $this->be($user = $this->createUser());

        $this->call('GET', '/home');

        $this->assertResponseOk();
        $this->assertCount(0, $this->view()->ownedLessons);
        $this->assertCount(0, $this->view()->subscribedLessons);
        $this->assertCount(0, $this->view()->availableLessons);
        $this->assertViewHas('userHasOwnedOrSubscribedLessons', false);
    }

    /** @test */
    public function itShould_notDisplayHomePage_unauthorized()
    {
        $this->call('GET', '/home');

        $this->assertResponseUnauthorized();
    }
}
