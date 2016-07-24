<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\SignupUserRequest;
use App\Models\User\User;
use App\Models\User\UserRepository;
use Illuminate\Http\Response;

class UserController extends Controller
{
    /**
     * @param SignupUserRequest $request
     * @param UserRepository $userRepository
     * @return Response
     */
    public function signup(SignupUserRequest $request, UserRepository $userRepository)
    {
        $user = $userRepository->create($request->all());
        return $this->response($user, Response::HTTP_CREATED);
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

        return $this->response($user);
    }
}
