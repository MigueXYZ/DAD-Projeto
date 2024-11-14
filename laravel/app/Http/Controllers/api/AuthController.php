<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Support\Str; // Add this at the top for the Str helper

class AuthController extends Controller
{
    private function purgeExpiredTokens()
    {
        // Only deletes if token expired 2 hours ago
        $dateTimetoPurge = now()->subHours(2);
        DB::table('personal_access_tokens')
            ->where('expires_at', '<', $dateTimetoPurge)->delete();
    }
    private function revokeCurrentToken(User $user)
    {
        $currentTokenId = $user->currentAccessToken()->id;
        $user->tokens()->where('id', $currentTokenId)->delete();
    }



    public function login(LoginRequest $request)
    {
        $this->purgeExpiredTokens();
        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Generate a random token
        $token = Str::random(60);

        // Save the token in the user's remember_token field
        $user = Auth::user();
        $user->remember_token = hash('sha256', $token); // Storing a hashed version for security
        $user->save();

        // Return the plain text token to the user
        return response()->json(['token' => $token]);
    }

    public function logout(Request $request)
    {
        $this->purgeExpiredTokens();

        $user = $request->user();
        $user->remember_token = null;
        $user->save();

        return response()->json(null, 204);
    }

    public function refreshToken(Request $request)
    {
        $this->purgeExpiredTokens();

        $user = $request->user();

        // Revoke the current token
        $user->remember_token = null;
        $user->save();

        // Generate and save a new token
        $newToken = Str::random(60);
        $user->remember_token = hash('sha256', $newToken); // Hash the new token
        $user->save();

        return response()->json(['token' => $newToken]);
    }

}
