<?php

namespace Tests\Http\Controllers\Web;

use Illuminate\Http\UploadedFile;
use Illuminate\View\View;
use PHPUnit_Framework_MockObject_MockObject;

class BaseTestCase extends \TestCase
{
    /**
     * @param $extension
     * @return PHPUnit_Framework_MockObject_MockObject|UploadedFile
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
    protected function view() : View
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

    /**
     * @param string $message
     */
    protected function assertErrorMessage(string $message)
    {
        $sessionMessages = session()->get('errors')->messages();
        if (isset($sessionMessages[0][0])) {
            $this->assertEquals($message, $sessionMessages[0][0]);
        } else {
            $this->fail('No error messages in session.');
        }
    }

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
}
