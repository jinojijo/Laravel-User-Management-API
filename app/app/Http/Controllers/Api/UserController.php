<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = User::query();
            
            // Add filtering capabilities
            if ($request->has('role')) {
                $query->where('role', $request->input('role'));
            }

            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Add sorting
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            
            if (in_array($sortBy, ['first_name', 'last_name', 'email', 'role', 'created_at', 'updated_at'])) {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Pagination
            $perPage = min($request->input('per_page', 15), 100); // Max 100 items per page
            $users = $query->paginate($perPage);

            Log::info('Users listing retrieved', [
                'total' => $users->total(),
                'per_page' => $perPage,
                'current_page' => $users->currentPage(),
                'filters' => $request->only(['role', 'search', 'sort_by', 'sort_order'])
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Users retrieved successfully',
                'data' => UserResource::collection($users),
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem(),
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Error retrieving users', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve users',
                'errors' => ['general' => ['An unexpected error occurred']]
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            
            $user = User::create($validatedData);

            Log::info('User created successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'User created successfully',
                'data' => new UserResource($user)
            ], 201);

        } catch (Exception $e) {
            Log::error('Error creating user', [
                'error' => $e->getMessage(),
                'input' => $request->except(['password']),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create user',
                'errors' => ['general' => ['An unexpected error occurred']]
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($user): JsonResponse
    {
        if(User::where('id',$user)->exists())
        {
            $user = User::where('id',$user)->first();
        }
        else{
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
                'errors' => ['general' => ['No user exists with the given ID']]
            ], 404);
        }
        try {
            Log::info('User details retrieved', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'User retrieved successfully',
                'data' => new UserResource($user)
            ]);

        } 
        catch (Exception $e) {
            Log::error('Error retrieving user', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve user',
                'errors' => ['general' => ['An unexpected error occurred']]
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            
            $originalData = $user->toArray();
            $user->update($validatedData);

            Log::info('User updated successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'updated_fields' => array_keys($validatedData),
                'original_data' => array_intersect_key($originalData, $validatedData),
                'new_data' => array_intersect_key($user->toArray(), $validatedData)
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'User updated successfully',
                'data' => new UserResource($user->fresh())
            ]);

        } catch (Exception $e) {
            Log::error('Error updating user', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
                'input' => $request->except(['password']),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update user',
                'errors' => ['general' => ['An unexpected error occurred']]
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): JsonResponse
    {
        try {
            $userData = $user->toArray();
            $user->delete();

            Log::warning('User deleted', [
                'user_id' => $userData['id'],
                'email' => $userData['email'],
                'deleted_user_data' => $userData
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'User deleted successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Error deleting user', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete user',
                'errors' => ['general' => ['An unexpected error occurred']]
            ], 500);
        }
    }
}