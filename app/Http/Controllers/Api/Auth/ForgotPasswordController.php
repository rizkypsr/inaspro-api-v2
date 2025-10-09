<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\ForgotPasswordRequest;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    use ApiResponse;

    /**
     * Handle a forgot password request for the application.
     */
    public function sendResetLinkEmail(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            // Send password reset link
            $status = Password::sendResetLink(
                $validated
            );

            if ($status === Password::RESET_LINK_SENT) {
                return $this->successResponse(
                    'Password reset link sent successfully',
                    [
                        'status' => 'We have emailed your password reset link!'
                    ]
                );
            }

            return $this->errorResponse(
                'Unable to send password reset link',
                [
                    'email' => ['We can\'t find a user with that email address.']
                ],
                400
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'An error occurred while sending password reset link',
                null,
                500
            );
        }
    }
}