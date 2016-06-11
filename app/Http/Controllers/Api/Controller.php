<?php

namespace App\Http\Controllers\Api;

use App\User;
use Auth;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;
use Illuminate\Http\Response;

class Controller extends BaseController
{
    use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests;

    /**
     * Http response with provided code, and status message content.
     * @param int $statusCode
     * @return Response
     */
    protected function status(int $statusCode) : Response
    {
        return Response::create(Response::$statusTexts[$statusCode], $statusCode);
    }

    /**
     * API response wrapper.
     * @param mixed $data
     * @param int $statusCode
     * @param array $headers
     * @return JsonResponse
     */
    protected function response($data = null, int $statusCode = 200, array $headers = [])
    {
        return JsonResponse::create($data, $statusCode, $headers);
    }

    /**
     * User authenticated with API token.
     * @return User
     */
    protected function user()
    {
        return Auth::guard('api')->user();
    }
}
