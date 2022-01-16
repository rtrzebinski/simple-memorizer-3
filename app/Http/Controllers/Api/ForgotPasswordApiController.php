<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordApiController extends ApiController
{
    /**
     * @param Request $request
     * @return JsonResponse|null
     */
    public function sendResetLinkEmail(Request $request): ?JsonResponse
    {
        $this->validate($request, ['email' => 'required|email']);

        $response = Password::broker()->sendResetLink(
            $request->only('email')
        );

        if ($response !== Password::RESET_LINK_SENT) {
            return $this->response('Unable to send reset link email.', 450);
        }

        return null;
    }
}
