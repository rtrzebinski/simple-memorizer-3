<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ApiController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * API response wrapper.
     * @param mixed $data
     * @param int $statusCode
     * @param array $headers
     * @return JsonResponse
     */
    protected function response($data = null, int $statusCode = 200, array $headers = []): JsonResponse
    {
        return JsonResponse::create($data, $statusCode, $headers);
    }

    /**
     * User authenticated with API token.
     * @return User|Authenticatable
     */
    protected function user(): User
    {
        return Auth::guard('api')->user();
    }
}
