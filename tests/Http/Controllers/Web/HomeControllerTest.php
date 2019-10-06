<?php

namespace Tests\Http\Controllers\Web;

class HomeControllerTest extends BaseTestCase
{
    // index

    /** @test */
    public function itShould_displayHomePage()
    {
        $this->be($user = $this->createUser());

        $this->call('GET', '/home');

        $this->assertResponseOk();
        $this->assertViewHas('ownedLessons', $user->ownedLessons()->with('exercises', 'subscribers')->get());
        $this->assertViewHas('subscribedLessons', $user->subscribedLessons()->with('exercises', 'subscribers')->get());
        $this->assertViewHas('availableLessons', $user->availableLessons());
        $this->assertViewHas('userHasOwnedOrSubscribedLessons', $user->hasOwnedOrSubscribedLessons());
    }

    /** @test */
    public function itShould_notDisplayHomePage_unauthorized()
    {
        $this->call('GET', '/home');

        $this->assertResponseUnauthorized();
    }
}
