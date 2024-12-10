<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GameResource;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class GameController extends Controller
{
    /**
     * Display a listing of the games.
     */
    public function index()
    {
        // Retrieve all games
        $games = Game::all();

        return GameResource::collection($games);
    }

    /**
     * Store a newly created game in storage.
     */
    public function store(Request $request): GameResource
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
            'total_turns_winner' => 'nullable|integer|min:1',
            'custom' => 'nullable|array', // Optional JSON field
        ]);

        // Create the game
        $game = Game::create($validated_data);

        return new GameResource($game); // 201 Created
    }

    /**
     * Display the specified game.
     */
    public function show(Game $game): GameResource
    {
        return new GameResource($game);
    }

    /**
     * Update the specified game in storage.
     */
    public function update(Request $request, Game $game): GameResource
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
            'total_turns_winner' => 'nullable|integer|min:1',
            'custom' => 'nullable|array',
        ], [
            'created_user_id.exists' => 'O criador deve ser um usuário válido.',
            'winner_user_id.exists' => 'O vencedor deve ser um usuário válido.',
            'type.in' => 'O tipo deve ser "S" (Singleplayer) ou "M" (Multiplayer).',
            'status.in' => 'O status deve ser "PE", "PL", "E" ou "I".',
            'ended_at.after_or_equal' => 'A data de término deve ser igual ou posterior à data de início.',
            'board_id.exists' => 'O tabuleiro deve ser válido.',
            'total_turns_winner.min' => 'O número de turnos do vencedor deve ser no mínimo 1.',
        ]);


        // Update the game
        $game->update($validated_data);

        return new GameResource($game); // 200 OK
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

    public function showMe(Request $request)
    {
        // Obtém o utilizador autenticado
        $user = $request->user();

        // Lê os parâmetros opcionais da query string
        $board = $request->query('board'); // Exemplo: /games/me?board=1
        $by = $request->query('by'); // Exemplo: /games/me?by=totaL_turns_winner
        $order = $request->query('order'); // Exemplo: /games/me?order=asc

        // Filtra jogos com base nos parâmetros
        $query = $user->games();

        if ($board) {
            $query->where('board_id', $board);
        }

        if ($by && in_array($order, ['asc', 'desc'])) {
            $query->orderBy($by, $order);
        }

        $games = $query->get();

        return GameResource::collection($games);
    }

}
