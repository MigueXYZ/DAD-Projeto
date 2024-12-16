<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::all();
        return response()->json($transactions);
    }

    public function store(Request $request)
    {
        // Validate incoming request data
        $validatedData = $request->validate([
            'transaction_datetime' => 'required|date',
            'user_id' => 'required|exists:users,id',
            'game_id' => 'nullable|exists:games,id',
            'type' => 'required|string',
            'euros' => 'nullable|integer|min:0|max:100',
            'brain_coins' => 'required|integer',
            'payment_type' => 'nullable|string',
            'payment_reference' => 'nullable|string',
            'custom' => 'nullable|array',
        ]);

        // Set game price if type is 'I'
        if ($validatedData['type'] === 'I') {
            $game = Game::findOrFail($validatedData['game_id']);
            //if game not found return error
            if (!$game) {
                return response()->json(['message' => 'Game not found'], 404);
            }
        }


        // If type is 'P', validate payment reference
        if ($validatedData['type'] === 'P') {
            if ($validatedData['payment_type']) {
                $this->validateReference($validatedData['payment_type'], $validatedData['payment_reference']);

                if ($validatedData['euros'] === null) {
                    return response()->json(['message' => 'Euros must be provided'], 400);
                }
            }
        }

        // Check if the user has enough brain coins
        $user = User::findOrFail($validatedData['user_id']);
        $temp = $user->brain_coins_balance + $validatedData['brain_coins'];

        if ($temp < 0) {
            return response()->json(['message' => 'Not enough brain coins'], 400);
        }

        // Update user's brain coins
        $user->brain_coins_balance = $temp;
        $user->save();

        // Create a new transaction
        $transaction = Transaction::create($validatedData);

        return response()->json($transaction, 201);
    }

    private function validateReference($type, $reference)
    {
        switch ($type) {
            case 'MBWAY':
                if (!preg_match('/^9\d{8}$/', $reference)) {
                    throw ValidationException::withMessages([
                        'payment_reference' => 'MBWAY reference must be 9 digits starting with 9.',
                    ]);
                }
                break;
            case 'PAYPAL':
                if (!filter_var($reference, FILTER_VALIDATE_EMAIL)) {
                    throw ValidationException::withMessages([
                        'payment_reference' => 'PAYPAL reference must be a valid email address.',
                    ]);
                }
                break;
            case 'IBAN':
                if (!preg_match('/^[A-Z]{2}\d{23}$/', $reference)) {
                    throw ValidationException::withMessages([
                        'payment_reference' => 'IBAN reference must start with 2 letters followed by 23 digits.',
                    ]);
                }
                break;
            case 'MB':
                if (!preg_match('/^\d{5}-\d{9}$/', $reference)) {
                    throw ValidationException::withMessages([
                        'payment_reference' => 'MB reference must follow the pattern: XXXXX-XXXXXXXXX.',
                    ]);
                }
                break;
            case 'VISA':
                if (!preg_match('/^4\d{15}$/', $reference)) {
                    throw ValidationException::withMessages([
                        'payment_reference' => 'VISA reference must be 16 digits starting with 4.',
                    ]);
                }
                break;
            default:
                throw ValidationException::withMessages([
                    'payment_reference' => 'Invalid payment type or missing payment reference.',
                ]);
        }
    }

    public function show($id)
    {
        $transaction = Transaction::findOrFail($id);
        return response()->json($transaction);
    }

    public function showMe(Request $request)
    {
        $user = $request->user();
        $transactions = Transaction::where('user_id', $user->id)
            ->orderBy('transaction_datetime', 'desc')
            ->select('id', 'user_id', 'transaction_datetime', 'type', 'payment_reference', 'brain_coins', 'euros')
            ->paginate(15);

        return response()->json([
            'data' => TransactionResource::collection($transactions->items()),
            'meta' => [
                'currentPage' => $transactions->currentPage(),
                'totalPages' => $transactions->lastPage(),
                'pageSize' => $transactions->perPage(),
                'totalItems' => $transactions->total(),
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

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

        $transaction->update($validatedData);
        return response()->json($transaction);
    }

    public function destroy($id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->delete();
        return response()->json(['message' => 'Transaction deleted successfully']);
    }
}
