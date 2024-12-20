<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class GameController extends Controller
{
    /**
     * Display a listing of the games.
     */
    public function index(Request $request)
    {

        // Lê os parâmetros opcionais da query string
        $board = $request->query('board');
        $by = $request->query('by');
        $order = $request->query('order');
        $ended= $request->query('ended');
        $type= $request->query('type');

        // Validar a ordem de ordenação
        if ($order && !in_array($order, ['asc', 'desc'])) {
            return response()->json(['error' => 'Invalid order direction'], 400);
        }


        $query = Game::with('MultiplayerGamesPlayed');

        if($ended){
            $query->where('status', 'E');
        }

        if($type){
            $query->where('type', $type);
        }

        // Filtra por board, se fornecido
        if ($board) {
            $query->where('board_id', $board);
        }

        // Ordena por parâmetros fornecidos
        if ($by && in_array($order, ['asc', 'desc'])) {
            $query->orderBy($by, $order);
            if($by === 'total_time'){
                $query->orderBy('total_turns_winner', 'asc');
            }
            if($by === 'total_turns_winner'){
                $query->orderBy('total_time', 'asc');
            }
        }

        // Pagina os resultados
        $games = $query->paginate(5);

        // Retorna os jogos com a resposta formatada
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
     * Verifica se o jogo do utilizador é um recorde.
     */
    public function checkForRecord(Game $game): int
    {
        if ($game->status !== 'E') {
            Log::info("Game is not finished: {$game->id}");
            return -2;
        }

        $user = User::find($game->created_user_id);

        if (!$user) {
            Log::error("User  not found: {$game->created_user_id}");
            return -1;
        }

        $ret=0;

        Log::info("User  found: {$user->id}");

        //get users personal record
        $topUserGames = Game::where('status', 'E')
            ->where('created_user_id', $user->id)
            ->where('board_id', $game->board_id)
            ->where('total_time', '>', 0)
            ->orderBy('total_time')
            ->limit(3)
            ->get();

        if (!$topUserGames || ($topUserGames->count() < 3 || $game->total_time < $topUserGames->last()->total_time)) {
            $user->increment('brain_coins_balance', 1);
            Log::info("User top beaten! User: {$user->id}, Game: {$game->id}, New balance: {$user->brain_coins_balance}");
            $ret+=1;
        }

        // Check global record
        $topGlobalGames = Game::where('status', 'E')
            ->where('board_id', $game->board_id)
            ->where('total_time', '>', 0)
            ->orderBy('total_time')
            ->limit(3)
            ->get();

        if ($topGlobalGames->isNotEmpty() && ($topGlobalGames->count() < 3 || $game->total_time < $topGlobalGames->last()->total_time)) {
            $user->increment('brain_coins_balance', 1);
            Log::info("Global top beaten! User: {$user->id}, Game: {$game->id}, New balance: {$user->brain_coins_balance}");
            $ret += 2;
        }

        return $ret;
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
    public function update(Request $request,  Game $game)
    {
        //confirmei e está bem
        //return $game;

        // Validar os dados da requisição
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

        //confirmei e está certo
        //return $validated_data;

        // Atualizar o jogo com os dados validados
        $game->update($validated_data);  // O $game já é o modelo, sem necessidade de busca adicional

        // Retornar o recurso de jogo atualizado
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
        $board = $request->query('board');
        $by = $request->query('by');
        $order = $request->query('order');
        $ended= $request->query('ended');
        $type= $request->query('type');

        // Validar a ordem de ordenação
        if ($order && !in_array($order, ['asc', 'desc'])) {
            return response()->json(['error' => 'Invalid order direction'], 400);
        }

        // Filtra jogos com base nos parâmetros
        $query = $user->games();

        if($ended){
            $query->where('status', 'E');
        }

        if($type){
            $query->where('type', $type);
        }

        // Filtra por board, se fornecido
        if ($board) {
            $query->where('board_id', $board);
        }

        // Ordena por parâmetros fornecidos
        if ($by && in_array($order, ['asc', 'desc'])) {
            $query->orderBy($by, $order);
            if($by === 'total_time'){
                $query->orderBy('total_turns_winner', 'asc');
            }
            if($by === 'total_turns_winner'){
                $query->orderBy('total_time', 'asc');
            }
        }

        // Pagina os resultados
        $games = $query->paginate(5);

        // Retorna os jogos com a resposta formatada
        return GameResource::collection($games);
    }

    public function storeMultiplayerGame(Game $game, Request $request)
    {
        //check if game is multiplayer
        if($game->type !== 'M'){
            return response()->json(['error' => 'This game is not multiplayer'], 400);
        }

        //check if player1_id exists
        $player1_id = $request->input('player1_id');
        $player1 = User::find($player1_id);
        if(!$player1){
            return response()->json(['error' => 'Player 1 not found'], 404);
        }

        //check if player2_id exists
        $player2_id = $request->input('player2_id');
        $player2 = User::find($player2_id);
        if(!$player2){
            return response()->json(['error' => 'Player 2 not found'], 404);
        }

        //create a new multiplayer game played record for player1
        $game->multiplayerGamesPlayed()->create([
            'user_id' => $player1_id,
            'game_id' => $game->id,
        ]);

        //create a new multiplayer game played record for player2
        $game->multiplayerGamesPlayed()->create([
            'user_id' => $player2_id,
            'game_id' => $game->id,
        ]);
    }

    public function getMultiplayerGames($gameId): JsonResponse
    {
        $game = Game::find($gameId);
        if(!$game){
            return response()->json(['error' => 'Game not found'], 404);
        }

        $multiplayerGames = $game->multiplayerGamesPlayed;

        return response()->json($multiplayerGames);
    }

    public function getNumGames(): JsonResponse
    {
        $numGames = Game::count();
        return response()->json(['num_games' => $numGames]);
    }

    public function getNumGamesByBoard(): JsonResponse
    {
        $numGamesByBoard = Game::select('board_id', Game::raw('count(*) as total'))
            ->groupBy('board_id')
            ->get();
        return response()->json($numGamesByBoard);
    }

    public function getNumGamesByGameMode(): JsonResponse
    {
        $numGamesByGameMode = Game::select('type', Game::raw('count(*) as total'))
            ->groupBy('type')
            ->get();
        return response()->json($numGamesByGameMode);
    }

    public function getScoreboard(Request $request): JsonResponse
    {
        // Scoreboard por tempo
        $scoreboard_time = Game::select('board_id', 'created_user_id', Game::raw('min(total_time) as min_time'))
            ->where('status', 'E')
            ->groupBy('board_id', 'created_user_id')
            ->get();

        // Scoreboard por turnos
        $scoreboard_turns = Game::select('board_id', 'created_user_id', Game::raw('min(total_turns_winner) as min_turns'))
            ->where('status', 'E')
            ->groupBy('board_id', 'created_user_id')
            ->get();

        $scoreboard = [
            'time' => $scoreboard_time,
            'turns' => $scoreboard_turns
        ];

        return response()->json($scoreboard);
    }


    public function getScoreboardMe(Request $request): JsonResponse
    {
        // Scoreboard por tempo para o usuário logado
        $scoreboard_time = Game::select('board_id', 'created_user_id', Game::raw('min(total_time) as min_time'))
            ->where('status', 'E')
            ->where('created_user_id', $request->user()->id)
            ->groupBy('board_id', 'created_user_id')
            ->get();

        // Scoreboard por turnos para o usuário logado
        $scoreboard_turns = Game::select('board_id', 'created_user_id', Game::raw('min(total_turns_winner) as min_turns'))
            ->where('status', 'E')
            ->where('created_user_id', $request->user()->id)
            ->groupBy('board_id', 'created_user_id')
            ->get();

        $scoreboard = [
            'time' => $scoreboard_time,
            'turns' => $scoreboard_turns
        ];

        return response()->json($scoreboard);
    }


}
