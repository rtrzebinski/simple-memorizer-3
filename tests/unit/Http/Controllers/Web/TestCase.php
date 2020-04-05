<?php

namespace Tests\Unit\Http\Controllers\Web;

use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;
use PHPUnit\Framework\MockObject\MockObject;

class TestCase extends \TestCase
{
    protected TestResponse $response;

    /**
     * Call the given URI.
     *
     * @param string $method
     * @param string $uri
     * @param array  $parameters
     * @param array  $cookies
     * @param array  $files
     * @param array  $server
     * @param string $content
     */
    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $this->response = parent::call($method, $uri, $parameters, $cookies, $files, $server, $content);
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

    protected function assertResponseUnauthorized()
    {
        $this->assertResponseStatus(Response::HTTP_FOUND);
        $this->assertResponseRedirectedTo('/login');
    }

    protected function assertResponseForbidden()
    {
        $this->assertResponseStatus(Response::HTTP_FORBIDDEN);
        $this->see('This action is unauthorized');
    }

    protected function assertResponseNotFound()
    {
        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
        $this->see('Not Found');
    }

    protected function assertResponseInvalidInput()
    {
        $this->assertResponseRedirectedBack();
        $this->response->assertSessionHasErrors();
    }

    protected function assertResponseRedirectedBack()
    {
        $this->assertResponseStatus(Response::HTTP_FOUND);
        $this->assertResponseRedirectedTo('http://localhost');
    }

    /**
     * @param string $uri
     */
    protected function assertResponseRedirectedTo(string $uri)
    {
        $this->response->assertRedirect($uri);
    }

    /**
     * Assert that the session has a given value.
     * @param      $key
     * @param null $value
     */
    protected function assertSessionHas($key, $value = null)
    {
        $this->response->assertSessionHas($key, $value);
    }

    /**
     * @param string $message
     */
    protected function assertSessionErrorMessage(string $message)
    {
        $sessionMessages = session()->get('errors')->messages();
        if (isset($sessionMessages[0][0])) {
            $this->assertEquals($message, $sessionMessages[0][0]);
        } else {
            $this->fail('No error messages in session.');
        }
    }

    /**
     * @param $extension
     * @return MockObject|UploadedFile
     */
    protected function createUploadedFileMock(string $extension)
    {
        $file = $this->createMock(UploadedFile::class);
        $file->method('getPath')->willReturn(uniqid());
        $file->method('isValid')->willReturn(true);
        $file->method('guessExtension')->willReturn($extension);
        return $file;
    }

    /**
     * @return View
     */
    protected function view(): View
    {
        return $this->response->original;
    }

    /**
     * Assert that the response view has a given piece of bound data.
     * @param      $key
     * @param null $value
     */
    protected function assertViewHas($key, $value = null)
    {
        $this->response->assertViewHas($key, $value);
    }

    /**
     * @param string $contents
     */
    protected function see(string $contents)
    {
        $this->response->assertSee($contents);
    }

    protected function dump()
    {
        dd($this->response->content());
    }
}
