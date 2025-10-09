<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\ResetPasswordRequest;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    use ApiResponse;

    /**
     * Handle a password reset request for the application.
     */
    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            // Reset the password
            $status = Password::reset(
                $validated,
                function ($user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                        'remember_token' => Str::random(60),
                    ])->save();
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return $this->successResponse(
                    'Password reset successful',
                    [
                        'status' => 'Your password has been reset successfully!'
                    ]
                );
            }

            return $this->errorResponse(
                'Password reset failed',
                [
                    'email' => ['This password reset token is invalid.']
                ],
                400
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'An error occurred while resetting password',
                null,
                500
            );
        }
    }
}