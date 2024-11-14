<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class GameController extends Controller
{
    /**
     * Display a listing of the games.
     */
    public function index(): JsonResponse
    {
        // Retrieve all games
        $games = Game::all();

        return response()->json($games, 200);
    }

    /**
     * Store a newly created game in storage.
     */
    public function store(Request $request): JsonResponse
    {
        // Validate the request data
        $validated_data = $request->validate([
            'created_user_id' => 'required|exists:users,id',
            'winner_user_id' => 'nullable|exists:users,id',
            'type' => ['required', Rule::in(['S', 'M'])], // S for single, M for multiplayer
            'status' => ['required', Rule::in(['PE', 'PL', 'E', 'I'])], // Game statuses
            'began_at' => 'required|date',
            'ended_at' => 'nullable|date|after_or_equal:began_at',
            'total_time' => 'nullable|numeric',
            'board_id' => 'required|exists:boards,id',
            'custom' => 'nullable|array', // Optional JSON field
        ]);

        // Create the game
        $game = Game::create($validated_data);

        return response()->json($game, 201); // 201 Created
    }

    /**
     * Display the specified game.
     */
    public function show(Game $game): JsonResponse
    {
        return response()->json($game, 200);
    }

    /**
     * Update the specified game in storage.
     */
    public function update(Request $request, Game $game): JsonResponse
    {
        // Validate the request data
        $validated_data = $request->validate([
            'created_user_id' => 'sometimes|exists:users,id',
            'winner_user_id' => 'nullable|exists:users,id',
            'type' => ['sometimes', Rule::in(['S', 'M'])],
            'status' => ['sometimes', Rule::in(['PE', 'PL', 'E', 'I'])],
            'began_at' => 'sometimes|date',
            'ended_at' => 'nullable|date|after_or_equal:began_at',
            'total_time' => 'nullable|numeric',
            'board_id' => 'sometimes|exists:boards,id',
            'custom' => 'nullable|array', // Optional JSON field
        ]);

        // Update the game
        $game->update($validated_data);

        return response()->json($game, 200); // 200 OK
    }

    /**
     * Remove the specified game from storage.
     */
    public function destroy(Game $game): JsonResponse
    {
        // Delete the game
        $game->delete();

        return response()->json(['message' => 'Game deleted successfully'], 204); // 204 No Content
    }
}
