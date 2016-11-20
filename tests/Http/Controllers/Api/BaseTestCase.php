<?php

namespace Tests\Http\Controllers\Api;

use App\Models\User\User;
use TestCase;
use Illuminate\Http\Response;

class BaseTestCase extends TestCase
{
    /**
     * Call api route as guest, or authenticated user.
     * @param string $method
     * @param string $uri
     * @param array $data
     * @param User|null $user
     * @return $this
     */
    protected function callApi(string $method, string $uri, array $data = [], User $user = null)
    {
        $headers = [];
        if ($user instanceof User) {
            $headers = ['Authorization' => 'Bearer ' . $user->api_token];
        }
        return parent::json($method, 'api' . $uri, $data, $headers);
    }

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
}
