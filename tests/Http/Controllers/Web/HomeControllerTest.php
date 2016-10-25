<?php

namespace Tests\Http\Controllers\Web;

class HomeControllerTest extends BaseTestCase
{
    public function testItShould_displayHomePage()
    {
        $this->be($user = $this->createUser());

        $this->call('GET', '/home');

        $this->assertResponseOk();
        $this->assertViewHas('ownedLessons', $user->ownedLessons);
        $this->assertViewHas('subscribedLessons', $user->subscribedLessons);
        $this->assertViewHas('availableLessons', $user->availableLessons());
        $this->assertViewHas('userHasOwnedOrSubscribedLessons', $user->hasOwnedOrSubscribedLessons());
    }

    public function testItShould_notDisplayHomePage_unauthorized()
    {
        $this->call('GET', '/home');

        $this->assertUnauthorized();
    }
}
