<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Traits\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    use ApiResponse;

    /**
     * Handle a login request to the application.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $user = User::where('email', $validated['email'])->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return $this->errorResponse(
                    'The provided credentials are incorrect.',
                    ['email' => ['The provided credentials are incorrect.']],
                    401
                );
            }

            // Create token for the user
            $token = $user->createToken('auth-token')->plainTextToken;

            return $this->successResponse(
                'Login successful',
                [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'email_verified_at' => $user->email_verified_at,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at,
                    ],
                    'token' => $token,
                    'token_type' => 'Bearer',
                ]
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'An error occurred during login',
                null,
                500
            );
        }
    }

    /**
     * Log the user out (Invalidate the token).
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->tokens()->delete();

            return $this->successResponse('Logout successful');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'An error occurred during logout',
                null,
                500
            );
        }
    }

    /**
     * Get the authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            return $this->successResponse(
                'User data retrieved successfully',
                [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'email_verified_at' => $user->email_verified_at,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at,
                    ]
                ]
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'An error occurred while retrieving user data',
                null,
                500
            );
        }
    }
}