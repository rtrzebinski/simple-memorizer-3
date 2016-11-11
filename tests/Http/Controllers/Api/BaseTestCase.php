<?php

namespace Tests\Http\Controllers\Api;

use TestCase;
use Illuminate\Http\Response;

class BaseTestCase extends TestCase
{
    protected function assertUnauthorised()
    {
        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
    }

    protected function assertInvalidInput()
    {
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    protected function assertForbidden()
    {
        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    protected function assertNotFound()
    {
        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }

    protected function assertInternalServerError()
    {
        $this->assertResponseStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
