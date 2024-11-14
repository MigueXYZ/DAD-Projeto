<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Game;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Display a listing of the transactions.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Fetch all transactions, or paginate them if needed
        $transactions = Transaction::all();

        return response()->json($transactions);
    }

    /**
     * Store a newly created transaction in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate incoming request data
        $validatedData = $request->validate([
            'transaction_datetime' => 'required|date',
            'user_id' => 'required|exists:users,id',
            'game_id' => 'required|exists:games,id',
            'type' => 'required|string',
            'euros' => 'required|numeric',
            'brain_coins' => 'required|integer',
            'payment_type' => 'required|string',
            'payment_reference' => 'required|string',
            'custom' => 'nullable|array',
        ]);

        // Create a new transaction
        $transaction = Transaction::create($validatedData);

        return response()->json($transaction, 201); // Return created transaction
    }

    /**
     * Display the specified transaction.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Find the transaction by ID, or return a 404 if not found
        $transaction = Transaction::findOrFail($id);

        return response()->json($transaction);
    }

    /**
     * Update the specified transaction in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Find the transaction
        $transaction = Transaction::findOrFail($id);

        // Validate incoming request data
        $validatedData = $request->validate([
            'transaction_datetime' => 'required|date',
            'user_id' => 'required|exists:users,id',
            'game_id' => 'required|exists:games,id',
            'type' => 'required|string',
            'euros' => 'required|numeric',
            'brain_coins' => 'required|integer',
            'payment_type' => 'required|string',
            'payment_reference' => 'required|string',
            'custom' => 'nullable|array',
        ]);

        // Update the transaction
        $transaction->update($validatedData);

        return response()->json($transaction);
    }

    /**
     * Remove the specified transaction from storage.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Find the transaction
        $transaction = Transaction::findOrFail($id);

        // Delete the transaction
        $transaction->delete();

        return response()->json(['message' => 'Transaction deleted successfully']);
    }
}
