<?php

namespace Tests\Http\Controllers\Web;

class BaseTestCase extends \TestCase
{
    protected function assertUnauthorized()
    {
        $this->assertResponseStatus(302);
        $this->assertRedirectedTo('/login');
    }

    protected function assertForbidden()
    {
        $this->assertResponseStatus(403);
        $this->see('This action is unauthorized.');
    }

    protected function assertNotFound()
    {
        $this->see('No query results for model');
        $this->assertResponseStatus(404);
    }

    protected function assertInvalidInput()
    {
        $this->assertRedirectedTo('http://localhost');
        $this->assertSessionHasErrors();
    }
}
