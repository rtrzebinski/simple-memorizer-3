<?php

use App\Models\User;
use Illuminate\Testing\TestResponse;
use Illuminate\Http\Response;

class ApiTestCase extends \TestCase
{
    protected TestResponse $response;

    /**
     * Call api route as guest, or authenticated user.
     * @param string    $method
     * @param string    $uri
     * @param array     $data
     * @param User|null $user
     */
    protected function callApi(string $method, string $uri, array $data = [], User $user = null)
    {
        $headers = [];
        if ($user instanceof User) {
            $headers = ['Authorization' => 'Bearer '.$user->api_token];
        }
        $this->response = parent::json($method, 'api'.$uri, $data, $headers);
    }

    /**
     * @param int $status
     */
    protected function assertResponseStatus(int $status)
    {
        $this->assertEquals($status, $this->response->status());
    }

    protected function assertResponseOk()
    {
        $this->assertResponseStatus(Response::HTTP_OK);
    }

    protected function assertResponseUnauthorised()
    {
        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
    }

    protected function assertResponseInvalidInput()
    {
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    protected function assertResponseForbidden()
    {
        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
    }

    protected function assertResponseNotFound()
    {
        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }

    /**
     * @param array $json
     */
    protected function seeJsonFragment(array $json)
    {
        $this->response->assertJsonFragment($json);
    }

    /**
     * @param array $json
     */
    protected function seeJson(array $json)
    {
        $this->response->assertJson($json);
    }

    protected function dump()
    {
        $this->response->dump();
    }
}
