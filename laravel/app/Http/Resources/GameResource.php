<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\Rule;

class GameResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    /*
     * // Validate the request data
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
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'created_user_id' => $this->created_user_id,
            'winner_user_id' => $this->winner_user_id,
            'type' => $this->type,
            'status' => $this->status,
            'began_at' => $this->began_at,
            'ended_at' => $this->ended_at,
            'total_time' => $this->total_time,
            'board_id' => $this->board_id,
            'custom' => $this->custom,
            'total_turns_winner' => $this->total_turns_winner,
        ];
    }
}
