<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MultiplayerGamesPlayedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /*
         * // Validate the request data
        $validated_data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'game_id' => 'required|exists:games,id',
            'player_won' => 'required|boolean',
            'pairs_discovered' => 'required|integer|min:0',
            'custom' => 'nullable|array', // Optional JSON field
        ]);
         */
        return [

            'user_id' => $this->user_id,
            'game_id' => $this->game_id,
            'player_won' => $this->player_won,
            'pairs_discovered' => $this->pairs_discovered,
            'custom' => $this->custom,

            ];
    }
}
