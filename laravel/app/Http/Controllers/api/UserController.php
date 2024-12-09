<?php

namespace App\Http\Controllers\api;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Psy\Util\Json;

class UserController extends Controller
{
    /**
     * Display a listing of the resource, including soft-deleted users if requested.
     */
    public function index(Request $request)
    {
        // If `with_deleted` query parameter is set, include soft-deleted users
        if ($request->query('with_deleted')) {
            $users = User::withTrashed()->get();
        } else {
            $users = User::all();
        }

        // Return the users with a 200 OK status
        return UserResource::collection($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): UserResource
    {
        // Validate the data
        $validated_data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|string|min:8',
            'type' => 'required|in:A,P',
            'nickname' => 'nullable|string|max:20',
            'photo_filename' => 'nullable|string|max:255',
            'blocked' => 'sometimes|boolean',
            'brain_coins_balance' => 'sometimes|integer|min:0',
            'custom' => 'nullable|array',  // Validate JSON as array
        ]);

        // Hash the password
        $validated_data['password'] = bcrypt($validated_data['password']);
        $validated_data['blocked'] = $validated_data['blocked'] ?? 0; // Default blocked to false
        $validated_data['brain_coins_balance'] = $validated_data['brain_coins_balance'] ?? 0; // Default balance to 0

        // Create the user
        $user = User::create($validated_data);

        // Return the user with a 201 Created status
        return new UserResource($user);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): UserResource
    {
        // Return the user with a 200 OK status
        // return response()->json($user, 200);
        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): UserResource
    {
        // Validate the data, email is unique except for the current user
        $validated_data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
            'password' => 'sometimes|string|min:8',
            'type' => 'sometimes|in:A,P',
            'nickname' => 'nullable|string|max:20',
            'photo_filename' => 'nullable|string|max:255',
            'blocked' => 'sometimes|boolean',
            'brain_coins_balance' => 'sometimes|integer|min:0',
            'custom' => 'nullable|array',  // Validate JSON as array
        ]);

        // Update password if it's provided
        if (isset($validated_data['password'])) {
            $validated_data['password'] = Hash::make($validated_data['password']);
        }

        // Update the user
        $user->update($validated_data);

        // Return the updated user with a 200 OK status
        return new UserResource($user);
    }

    /**
     * Soft delete the specified resource from storage.
     */
    public function destroy(User $user): JsonResponse
    {
        // Soft delete the user
        $user->delete();

        // Return a success message with a 204 No Content status
        return response()->json(['message' => 'User deleted successfully'], 204);
    }

    /**
     * Restore a soft-deleted user.
     */
    public function restore($id): JsonResponse
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return response()->json(['message' => 'User restored successfully'], 200);
    }

    public function showMe(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    // Get the names of all users
    public function getNames():JsonResponse
    {
        $users = User::all();
        $names = $users->map(function ($user) {
            return $user->name;
        });

        return response()->json($names, 200);
    }

    /**
     * Update the authenticated user.
     */
    public function updateMe(Request $request): UserResource
    {
        // Validate the data, email is unique except for the current user
        $validated_data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('users')->ignore($request->user()->id)
            ],
            'password' => 'sometimes|string|min:8',
            'nickname' => 'nullable|string|max:20',
            'photo_filename' => 'nullable|string|max:255',
            'brain_coins_balance' => 'sometimes|integer|min:0',
            'custom' => 'nullable|array',  // Validate JSON as array
        ]);

        // Update password if it's provided
        if (isset($validated_data['password'])) {
            $validated_data['password'] = Hash::make($validated_data['password']);
        }

        // Update the user
        $request->user()->update($validated_data);

        // Return the updated user with a 200 OK status
        return new UserResource($request->user());
    }
}
