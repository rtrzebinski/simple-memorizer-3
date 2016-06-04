<?php

namespace App\Http\Controllers\Api;

use App\User;
use App\Repositories\UserRepository;
use App\Http\Requests\Api\UserSignupRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        $userRepository->create($request->all());

        return $this->createResponse(Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @param UserRepository $userRepository
     * @return Response
     */
    public function login(Request $request, UserRepository $userRepository)
    {
        $user = $userRepository->findByCredentials($request->email, $request->password);

        if (!$user instanceof User) {
            return $this->createResponse(Response::HTTP_UNAUTHORIZED);
        }

        return JsonResponse::create([
                'token' => $user->api_token
            ]
        );
    }
}
