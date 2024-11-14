<?php

namespace App\Http\Controllers\api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the resource, including soft-deleted users if requested.
     */
    public function index(Request $request): JsonResponse
    {
        // If `with_deleted` query parameter is set, include soft-deleted users
        if ($request->query('with_deleted')) {
            $users = User::withTrashed()->get();
        } else {
            $users = User::all();
        }

        return response()->json($users, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
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
        return response()->json($user, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): JsonResponse
    {
        // Return the user with a 200 OK status
        return response()->json($user, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): JsonResponse
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
        return response()->json($user, 200);
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
}
