<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoardResource;
use App\Models\Board;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BoardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Retrieve all boards
        $boards = Board::all();
        return BoardResource::collection($boards);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        // Validate the request data
        $validated_data = $request->validate([
            'board_cols' => 'required|integer|min:1',
            'board_rows' => 'required|integer|min:1',
            'custom' => 'nullable|array', // Optional JSON field
        ]);

        // Create the board
        $board = Board::create($validated_data);

        return response()->json($board, 201); // 201 Created
    }

    /**
     * Display the specified resource.
     */
    public function show(Board $board): JsonResponse
    {
        return response()->json($board, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Board $board): JsonResponse
    {
        // Validate the request data
        $validated_data = $request->validate([
            'board_cols' => 'sometimes|integer|min:1',
            'board_rows' => 'sometimes|integer|min:1',
            'custom' => 'nullable|array', // Optional JSON field
        ]);

        // Update the board
        $board->update($validated_data);

        return response()->json($board, 200); // 200 OK
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Board $board): JsonResponse
    {
        // Delete the board
        $board->delete();

        return response()->json(['message' => 'Board deleted successfully'], 204); // 204 No Content
    }
}
