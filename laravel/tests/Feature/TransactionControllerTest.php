<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Game;
use Illuminate\Testing\Fluent\AssertableJson;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase; // Ensures that the database is reset between tests

    /**
     * Test that we can retrieve all transactions.
     *
     * @return void
     */
    public function test_index_returns_all_transactions()
    {
        // Create some test data
        $user = User::factory()->create();
        $game = Game::factory()->create();
        Transaction::factory()->count(3)->create(['user_id' => $user->id, 'game_id' => $game->id]);

        // Send GET request to the index route
        $response = $this->getJson('/api/transactions');

        // Assert that the response is successful and returns a list of transactions
        $response->assertStatus(200)
                 ->assertJsonCount(3);
    }

    /**
     * Test that a transaction can be created.
     *
     * @return void
     */
    public function test_store_creates_transaction()
    {
        // Create related models
        $user = User::factory()->create();
        $game = Game::factory()->create();

        // Prepare valid data for creating a transaction
        $data = [
            'transaction_datetime' => now()->toString(),
            'user_id' => $user->id,
            'game_id' => $game->id,
            'type' => 'B',
            'euros' => 10.50,
            'brain_coins' => 100,
            'payment_type' => 'MBWAY',
            'payment_reference' => '123456789',
            'custom' => ['notes' => 'Test transaction'],
        ];

        // Send POST request to the store route
        $response = $this->postJson('/api/transactions', $data);

        // Assert the response status and the created data
        $response->assertStatus(201)
                 ->assertJsonFragment([
                     'user_id' => $user->id,
                     'game_id' => $game->id,
                     'type' => 'B',
                     'euros' => 10.50,
                 ]);
    }

    /**
     * Test that we can view a single transaction.
     *
     * @return void
     */
    public function test_show_returns_transaction()
    {
        // Create related models and a transaction
        $user = User::factory()->create();
        $game = Game::factory()->create();
        $transaction = Transaction::factory()->create(['user_id' => $user->id, 'game_id' => $game->id]);

        // Send GET request to the show route
        $response = $this->getJson('/api/transactions/' . $transaction->id);

        // Assert that the response contains the transaction data
        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'user_id' => $user->id,
                     'game_id' => $game->id,
                     'type' => $transaction->type,
                     'euros' => $transaction->euros,
                 ]);
    }

    /**
     * Test that we can update an existing transaction.
     *
     * @return void
     */
    public function test_update_modifies_transaction()
    {
        // Create related models and a transaction
        $user = User::factory()->create();
        $game = Game::factory()->create();
        $transaction = Transaction::factory()->create(['user_id' => $user->id, 'game_id' => $game->id]);

        // Prepare new data for updating the transaction
        $data = [
            'transaction_datetime' => now()->toString(),
            'user_id' => $user->id,
            'game_id' => $game->id,
            'type' => 'refund',
            'euros' => 5.00,
            'brain_coins' => 50,
            'payment_type' => 'paypal',
            'payment_reference' => 'xyz456',
            'custom' => ['notes' => 'Refund transaction'],
        ];

        // Send PUT request to update the transaction
        $response = $this->putJson('/api/transactions/' . $transaction->id, $data);

        // Assert the response is successful and the transaction is updated
        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'type' => 'refund',
                     'euros' => 5.00,
                     'payment_type' => 'paypal',
                 ]);
    }

    /**
     * Test that a transaction can be deleted.
     *
     * @return void
     */
    public function test_destroy_deletes_transaction()
    {
        // Create related models and a transaction
        $user = User::factory()->create();
        $game = Game::factory()->create();
        $transaction = Transaction::factory()->create(['user_id' => $user->id, 'game_id' => $game->id]);

        // Send DELETE request to destroy the transaction
        $response = $this->deleteJson('/api/transactions/' . $transaction->id);

        // Assert that the transaction was deleted
        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Transaction deleted successfully'
                 ]);

        // Assert that the transaction no longer exists in the database
        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
    }
}
