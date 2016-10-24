<?php

namespace Tests\Http\Controllers\Web;

use TestCase;

class HomeControllerTest extends TestCase
{
    public function testItShould_displayHomePage()
    {
        $this->be($user = $this->createUser());

        $this->call('GET', '/home');

        $this->assertResponseOk();
        $this->assertViewHas('ownedLessons', $user->ownedLessons);
        $this->assertViewHas('subscribedLessons', $user->subscribedLessons);
        $this->assertViewHas('availableLessons', $user->availableLessons());
    }

    public function testItShould_notDisplayHomePage_notAuthenticatedUser()
    {
        $this->call('GET', '/home');

        $this->assertRedirectedTo('/login');
    }
}
