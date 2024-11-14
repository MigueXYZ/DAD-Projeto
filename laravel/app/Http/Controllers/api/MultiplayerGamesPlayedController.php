<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\MultiplayerGamesPlayed;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MultiplayerGamesPlayedController extends Controller
{
    /**
     * Display a listing of the multiplayer games played.
     */
    public function index(): JsonResponse
    {
        // Retrieve all multiplayer games played
        $gamesPlayed = MultiplayerGamesPlayed::all();

        return response()->json($gamesPlayed, 200);
    }

    /**
     * Store a newly created multiplayer game record.
     */
    public function store(Request $request): JsonResponse
    {
        // Validate the request data
        $validated_data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'game_id' => 'required|exists:games,id',
            'player_won' => 'required|boolean',
            'pairs_discovered' => 'required|integer|min:0',
            'custom' => 'nullable|array', // Optional JSON field
        ]);

        // Create a new multiplayer game record
        $multiplayerGame = MultiplayerGamesPlayed::create($validated_data);

        return response()->json($multiplayerGame, 201); // 201 Created
    }

    /**
     * Display the specified multiplayer game record.
     */
    public function show(MultiplayerGamesPlayed $multiplayerGame): JsonResponse
    {
        return response()->json($multiplayerGame, 200);
    }

    /**
     * Update the specified multiplayer game record.
     */
    public function update(Request $request, MultiplayerGamesPlayed $multiplayerGame): JsonResponse
    {
        // Validate the request data
        $validated_data = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'game_id' => 'sometimes|exists:games,id',
            'player_won' => 'sometimes|boolean',
            'pairs_discovered' => 'sometimes|integer|min:0',
            'custom' => 'nullable|array', // Optional JSON field
        ]);

        // Update the multiplayer game record
        $multiplayerGame->update($validated_data);

        return response()->json($multiplayerGame, 200); // 200 OK
    }

    /**
     * Remove the specified multiplayer game record from storage.
     */
    public function destroy(MultiplayerGamesPlayed $multiplayerGame): JsonResponse
    {
        // Delete the multiplayer game record
        $multiplayerGame->delete();

        return response()->json(['message' => 'Multiplayer game record deleted successfully'], 204); // 204 No Content
    }
}
