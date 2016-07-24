<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\LoginUserRequest;
use App\Http\Requests\Api\UserSignupRequest;
use App\Models\User\User;
use App\Models\User\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class UserController extends Controller
{
    /**
     * @param UserSignupRequest $request
     * @param UserRepository $userRepository
     * @return Response
     */
    public function signup(UserSignupRequest $request, UserRepository $userRepository)
    {
        $user = $userRepository->create($request->all());
        return JsonResponse::create($user)->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @param LoginUserRequest $request
     * @param UserRepository $userRepository
     * @return Response
     */
    public function login(LoginUserRequest $request, UserRepository $userRepository)
    {
        $user = $userRepository->findByCredentials($request->email, $request->password);

        if (!$user instanceof User) {
            return $this->status(Response::HTTP_UNAUTHORIZED);
        }

        return JsonResponse::create($user)->setStatusCode(Response::HTTP_OK);
    }
}
