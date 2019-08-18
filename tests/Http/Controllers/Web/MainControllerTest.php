<?php

namespace Tests\Http\Controllers\Web;

class MainControllerTest extends BaseTestCase
{
    public function testItShould_displayMainPage_authenticatedUser()
    {
        $this->be($user = $this->createUser());

        $this->call('GET', '/');

        $this->assertResponseRedirectedTo('/home');
    }

    public function testItShould_displayMainPage_notAuthenticatedUser()
    {
        $this->call('GET', '/');

        $this->assertResponseOk();
    }
}
