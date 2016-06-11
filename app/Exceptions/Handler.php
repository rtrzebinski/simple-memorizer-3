<?php

namespace App\Exceptions;

use App;
use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        return parent::render($request, $e);
    }

    /**
     * Create a Symfony response for the given exception.
     *
     * @param  \Exception $exception
     * @return Response
     */
    protected function convertExceptionToResponse(Exception $exception)
    {
        if (App::runningInConsole()) {
            // display exception response in console readable form
            $message = $exception->getMessage() . PHP_EOL . $exception->getTraceAsString();
            $statusCode = FlattenException::create($exception)->getStatusCode();
            return \Illuminate\Http\Response::create($message, $statusCode);
        }
        return parent::convertExceptionToResponse($exception);
    }

}
