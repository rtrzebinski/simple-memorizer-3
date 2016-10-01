<?php

namespace Tests\Http\Controllers\Web;

use TestCase;

class MainControllerTest extends TestCase
{
    public function testItShould_displayMainPage_authenticatedUser()
    {
        $this->be($user = $this->createUser());

        $this->call('GET', '/');

        $this->assertRedirectedTo('/home');
    }

    public function testItShould_displayMainPage_notAuthenticatedUser()
    {
        $this->call('GET', '/');

        $this->assertResponseOk();
    }
}
