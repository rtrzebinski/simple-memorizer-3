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
        $this->see('This action is unauthorized');
    }

    protected function assertNotFound()
    {
        $this->see('No query results for model');
        $this->assertResponseStatus(404);
    }

    protected function assertInvalidInput()
    {
        $this->assertRedirectedBack();
        $this->assertSessionHasErrors();
    }

    protected function assertRedirectedBack()
    {
        $this->assertResponseStatus(302);
        $this->assertRedirectedTo('http://localhost');
    }

    protected function view()
    {
        return $this->response->original;
    }

    /**
     * Dump current session and exit.
     */
    public function dumpSession()
    {
        dd(\Session::all());
    }
}
