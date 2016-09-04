<?php

namespace App\Http\Controllers\Api;

use App\Models\User\User;
use App\Models\User\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserController extends Controller
{
    /**
     * @param Request $request
     * @param UserRepository $userRepository
     * @return Response
     */
    public function signup(Request $request, UserRepository $userRepository)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = $userRepository->create($request->all());

        return $this->response($user->makeVisible('api_token'), Response::HTTP_CREATED);
    }

    /**
     * @param Request $request
     * @param UserRepository $userRepository
     * @return Response
     */
    public function login(Request $request, UserRepository $userRepository)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = $userRepository->findByCredentials($request->email, $request->password);

        if (!$user instanceof User) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED);
        }

        return $this->response($user->makeVisible('api_token'));
    }
}
