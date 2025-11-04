<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Traits\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    use ApiResponse;

    /**
     * Handle a registration request for the application.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
            ]);

            // Create token for the user
            $token = $user->createToken('auth-token')->plainTextToken;

            return $this->successResponse(
                'Registration successful',
                [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'email_verified_at' => $user->email_verified_at,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at,
                    ],
                    'token' => $token,
                    'token_type' => 'Bearer',
                ],
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'An error occurred during registration',
                null,
                500
            );
        }
    }
}