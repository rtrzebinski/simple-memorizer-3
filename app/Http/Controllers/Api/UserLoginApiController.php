<?php

namespace App\Http\Controllers\Api;

use App\Models\UserRepository;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserLoginApiController extends ApiController
{
    /**
     * @param Request $request
     * @param UserRepository $userRepository
     * @return JsonResponse
     */
    public function login(Request $request, UserRepository $userRepository)
    {
        $this->validate(
            $request,
            [
                'email' => 'required|email',
                'password' => 'required',
            ]
        );

        $user = $userRepository->findByCredentials($request->email, $request->password);

        if (!$user instanceof User) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED);
        }

        return $this->response($user->makeVisible('api_token'));
    }
}
