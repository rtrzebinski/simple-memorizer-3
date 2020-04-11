<?php

namespace App\Http\Controllers\Api;

use App\Models\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    /**
     * @param Request        $request
     * @param UserRepository $userRepository
     * @return JsonResponse
     */
    public function register(Request $request, UserRepository $userRepository)
    {
        $this->validate($request, [
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6',
        ]);

        $user = $userRepository->create($request->all());

        return $this->response($user->makeVisible('api_token'));
    }
}
