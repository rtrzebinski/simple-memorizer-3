<?php

namespace Tests\Http\Controllers\Web;

class MainControllerTest extends BaseTestCase
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
        $this->call('GET', '/');

        $this->assertResponseOk();
    }
}
