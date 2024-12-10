<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class GameController extends Controller
{
    /**
     * Display a listing of the games.
     */
    public function index(Request $request)
    {
        $user = $request->user(); // Obter o utilizador autenticado, se existir

        // Lê os parâmetros da query string (filtros)
        $board = $request->query('board'); // Filtro por board
        $by = $request->query('by'); // Filtro por ordenação
        $order = $request->query('order'); // Direção da ordenação


            // Se o utilizador não está autenticado, mostramos apenas o scoreboard global
            $query = Game::where('status', 'E'); // Só jogos terminados


        // Aplicar filtro por board, se existir
        if ($board) {
            $query->where('board_id', $board);
        }

        // Aplicar filtro por ordenação, se existir
        if ($by && in_array($order, ['asc', 'desc'])) {
            $query->orderBy($by, $order);
        }

        // Filtra jogos globalmente ou apenas os do utilizador autenticado
        $games = $query->get();

        // Retorna os jogos como um recurso JSON
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

        // Verificar se o jogo é um recorde
        if ($game->status === 'E') {
            $this->checkForRecord($game);
        }

        return new GameResource($game); // 201 Created
    }

    /**
     * Verifica se o jogo do utilizador é um recorde.
     */
    private function checkForRecord(Game $game)
    {
        $user = User::find($game->created_user_id);

        if (!$user) {
            return; // Caso o utilizador não exista
        }

        // Verificar se o utilizador bateu o recorde pessoal
        $topUserGame = Game::where('created_user_id', $game->created_user_id)
            ->where('total_time', '>', 0)
            ->orderBy('total_time')
            ->limit(1)
            ->get();

        // Se o utilizador tiver menos de 10 jogos, ou se o tempo do novo jogo for melhor do que o pior dos 10 melhores jogos
        if ($game->total_time < $topUserGame->total_time) {
            // Adicionar 1 brain coin para o recorde pessoal
            $user->increment('brain_coins_balance');
            $user->save();
        }

        // Verificar se o utilizador bateu um dos top 3 melhores tempos globais
        $topGlobalGames = Game::where('status', 'E')
            ->where('total_time', '>', 0)
            ->orderBy('total_time')
            ->limit(3)
            ->get();

        // Se o tempo do jogo atual for melhor do que o pior tempo entre os 3 melhores jogos globais
        if ($game->total_time < $topGlobalGames->last()->total_time) {
            // Adicionar 1 brain coin para o top 3 global
            $user->increment('brain_coins_balance');
            $user->save();
        }
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

        // Verificar se o jogo é um recorde
        if ($game->status === 'E') {
            $this->checkForRecord($game);
        }

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
