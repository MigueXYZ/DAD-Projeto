<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MultiplayerGamesPlayedResource;
use App\Models\MultiplayerGamesPlayed;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MultiplayerGamesPlayedController extends Controller
{
    /**
     * Display a listing of the multiplayer games played.
     */
    public function index()
    {
        // Get all multiplayer games played
        $multiplayerGames = MultiplayerGamesPlayed::all();

        return MultiplayerGamesPlayedResource::collection($multiplayerGames);
    }

    /**
     * Store a newly created multiplayer game record.
     */
    public function store(Request $request): MultiplayerGamesPlayedResource
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

        return new MultiplayerGamesPlayedResource($multiplayerGame); // 201 Created
    }

    /**
     * Display the specified multiplayer game record.
     */
    public function show(MultiplayerGamesPlayed $multiplayerGame): MultiplayerGamesPlayedResource
    {
        return new MultiplayerGamesPlayedResource($multiplayerGame);
    }

    /**
     * Update the specified multiplayer game record.
     */
    public function update(Request $request, MultiplayerGamesPlayed $multiplayerGame): MultiplayerGamesPlayedResource
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

        return new MultiplayerGamesPlayedResource($multiplayerGame); // 200 OK
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

    public function updateIt(Request $request)
    {
        // Obter o registro que corresponde aos critérios
        $multiplayerGamePlayed = MultiplayerGamesPlayed::where('user_id', $request->user_id)
            ->where('game_id', $request->game_id)
            ->first();

        // Verificar se o registro existe
        if (!$multiplayerGamePlayed) {
            return response()->json(['error' => 'Registro não encontrado'], 404);
        }

        // Atualizar o registro com os dados recebidos
        $multiplayerGamePlayed->update($request->only(['player_won', 'pairs_discovered']));

        // Retornar o recurso atualizado
        return new MultiplayerGamesPlayedResource($multiplayerGamePlayed);
    }

    public function getScoreboard()
    {
        // Obter os registros de partidas multiplayer com informações dos jogos
        $multiplayerGames = MultiplayerGamesPlayed::with('game.board')->get();

        // Agrupar os registros por tabuleiro (board)
        $scoreboard = $multiplayerGames->groupBy(function ($gamePlayed) {
            return $gamePlayed->game->board_id;
        });

        // Processar cada grupo (board) para criar os scoreboards
        $scoreboard = $scoreboard->map(function ($gamesByBoard, $boardId) {
            // Agrupar por utilizador dentro do tabuleiro
            $groupedByUser = $gamesByBoard->groupBy('user_id');

            // Calcular a pontuação de cada user
            $userScores = $groupedByUser->map(function ($games) {
                $victories = $games->sum('player_won');
                return [
                    'user_id' => $games->first()->user_id,
                    'victories' => $victories,
                    'defeats' => $games->count() - $victories,
                ];
            });

            // Ordenar por vitórias e depois por derrotas
            $userScores = $userScores->sortByDesc('victories')->sortBy('defeats');

            // Limitar aos 5 melhores jogadores
            $topPlayers = $userScores->take(5);

            return [
                'board_id' => $boardId,
                'scoreboard' => $topPlayers->values(),
            ];
        });

        //order by board_id
        $scoreboard = $scoreboard->sortBy('board_id');

        return response()->json($scoreboard->values());
    }


}
