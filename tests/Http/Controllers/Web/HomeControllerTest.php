<?php

namespace Tests\Http\Controllers\Web;

class HomeControllerTest extends BaseTestCase
{
    // index

    /** @test */
    public function itShould_displayHomePage()
    {
        $this->be($user = $this->createUser());
        // availableLessons
        $this->createLesson();
        // subscribedLessons
        $subscribed = $this->createLesson();
        $subscribed->subscribe($user);
        // ownedLessons
        $this->createPublicLesson($user);
        $this->createPrivateLesson($user);

        $this->call('GET', '/home');

        $this->assertResponseOk();
        $this->assertViewHas('ownedLessons', $user->ownedLessons()->with('exercises', 'subscribedUsers')->get());
        $this->assertCount(2, $this->view()->ownedLessons);
        $this->assertViewHas('subscribedLessons', $user->subscribedLessons()->with('exercises', 'subscribedUsers')->get());
        $this->assertCount(1, $this->view()->subscribedLessons);
        $this->assertViewHas('availableLessons', $user->availableLessons());
        $this->assertCount(1, $this->view()->availableLessons);
        $this->assertViewHas('userHasOwnedOrSubscribedLessons', true);
    }

    /** @test */
    public function itShould_displayHomePage_userHasNoOwnedOrSubscribedLessons()
    {
        $this->be($user = $this->createUser());

        $this->call('GET', '/home');

        $this->assertResponseOk();
        $this->assertViewHas('ownedLessons', $user->ownedLessons()->with('exercises', 'subscribedUsers')->get());
        $this->assertViewHas('subscribedLessons', $user->subscribedLessons()->with('exercises', 'subscribedUsers')->get());
        $this->assertViewHas('availableLessons', $user->availableLessons());
        $this->assertViewHas('userHasOwnedOrSubscribedLessons', false);
    }

    /** @test */
    public function itShould_notDisplayHomePage_unauthorized()
    {
        $this->call('GET', '/home');

        $this->assertResponseUnauthorized();
    }
}
