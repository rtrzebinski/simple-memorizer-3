<?php

namespace Tests\Http\Controllers\Web;

use TestCase;

class HomeControllerTest extends TestCase
{
    public function testItShould_displayHomePage_authenticatedUser()
    {
        $this->be($user = $this->createUser());

        $this->call('GET', '/home');

        $this->assertResponseOk();
    }

    public function testItShould_notDisplayHomePage_notAuthenticatedUser()
    {
        $this->call('GET', '/home');

        $this->assertRedirectedTo('/login');
    }
}
