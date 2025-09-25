<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Exception;

class AuthController extends Controller
{
    /**
     * Login user and create token
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                Log::warning('Failed login attempt', [
                    'email' => $request->email,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);

                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            // Revoke all existing tokens
            $user->tokens()->delete();

            $token = $user->createToken('auth_token')->plainTextToken;

            Log::info('User logged in successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'data' => [
                    'user' => new UserResource($user),
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            Log::error('Login error', [
                'error' => $e->getMessage(),
                'email' => $request->email ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Login failed',
                'errors' => ['general' => ['An unexpected error occurred']]
            ], 500);
        }
    }

    /**
     * Register a new user
     */
    public function register(StoreUserRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            
            $user = User::create($validatedData);
            $token = $user->createToken('auth_token')->plainTextToken;

            Log::info('User registered successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Registration successful',
                'data' => [
                    'user' => new UserResource($user),
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ], 201);

        } catch (Exception $e) {
            Log::error('Registration error', [
                'error' => $e->getMessage(),
                'input' => $request->except(['password']),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Registration failed',
                'errors' => ['general' => ['An unexpected error occurred']]
            ], 500);
        }
    }

    /**
     * Logout user (Revoke the token)
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $user->currentAccessToken()->delete();

            Log::info('User logged out successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Logout successful'
            ]);

        } catch (Exception $e) {
            Log::error('Logout error', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Logout failed',
                'errors' => ['general' => ['An unexpected error occurred']]
            ], 500);
        }
    }

    /**
     * Get the authenticated User
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            return response()->json([
                'status' => 'success',
                'message' => 'User data retrieved successfully',
                'data' => new UserResource($user)
            ]);

        } catch (Exception $e) {
            Log::error('Error retrieving user data', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve user data',
                'errors' => ['general' => ['An unexpected error occurred']]
            ], 500);
        }
    }

    /**
     * Refresh a token
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Revoke current token
            $user->currentAccessToken()->delete();
            
            // Create new token
            $token = $user->createToken('auth_token')->plainTextToken;

            Log::info('Token refreshed successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Token refreshed successfully',
                'data' => [
                    'user' => new UserResource($user),
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Token refresh error', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Token refresh failed',
                'errors' => ['general' => ['An unexpected error occurred']]
            ], 500);
        }
    }
}