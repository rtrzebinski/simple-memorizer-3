<?php

namespace Tests\Http\Controllers\Web;

use TestCase;

class MainControllerTest extends TestCase
{
    public function testItShould_displayHomePage_authenticatedUser()
    {
        $this->be($user = $this->createUser());

        $this->call('GET', '/');

        $this->assertResponseOk();
    }

    public function testItShould_displayHomePage_notAuthenticatedUser()
    {
        $this->call('GET', '/');

        $this->assertResponseOk();
    }
}
