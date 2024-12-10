<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the index method returns a list of users.
     *
     * @return void
     */
    public function test_index_returns_all_users()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
                 ->assertJsonCount(2) // Checks if 2 users are returned
                 ->assertJsonFragment(['name' => $user1->name])
                 ->assertJsonFragment(['name' => $user2->name]);
    }

    /**
     * Test that the store method creates a new user.
     *
     * @return void
     */
    public function test_store_creates_user()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'password',
            'type' => 'A',
            'nickname' => 'johnny',
            'photo_filename' => 'profile.jpg',
            'blocked' => false,
            'brain_coins_balance' => 100,
        ];

        $response = $this->postJson('/api/users', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['name' => 'John Doe'])
                 ->assertJsonFragment(['email' => 'johndoe@example.com']);

        $this->assertDatabaseHas('users', [
            'email' => 'johndoe@example.com',
        ]);
    }

    /**
     * Test that the show method returns a single user.
     *
     * @return void
     */
    public function test_show_returns_user()
    {
        $user = User::factory()->create();

        $response = $this->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => $user->name])
                 ->assertJsonFragment(['email' => $user->email]);
    }

    /**
     * Test that the update method updates a user.
     *
     * @return void
     */
    public function test_update_modifies_user()
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'password' => 'newpassword',
            'type' => 'P',
            'nickname' => 'updated_nick',
            'photo_filename' => 'updated_profile.jpg',
            'blocked' => true,
            'brain_coins_balance' => 200,
        ];

        $response = $this->putJson("/api/users/{$user->id}", $data);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Updated Name'])
                 ->assertJsonFragment(['email' => 'updated@example.com']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'updated@example.com',
        ]);
    }

    /**
     * Test that the destroy method soft deletes a user.
     *
     * @return void
     */
    public function test_destroy_deletes_user()
    {
        $user = User::factory()->create();

        $response = $this->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);
    }

    /**
     * Test that the restore method restores a soft-deleted user.
     *
     * @return void
     */
    public function test_restore_restores_user()
    {
        $user = User::factory()->create();

        // Soft delete the user
        $user->delete();

        // Now restore the user
        $response = $this->postJson("/api/users/restore/{$user->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'User restored successfully']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'deleted_at' => null,
        ]);
    }

    /**
     * Test that the index method returns users, including soft-deleted ones when requested.
     *
     * @return void
     */
    public function test_index_with_deleted_returns_all_users_including_deleted()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();
        $user3->delete(); // Soft delete a user

        $response = $this->getJson('/api/users?with_deleted=true');

        $response->assertStatus(200)
                 ->assertJsonCount(3) // Includes the deleted user
                 ->assertJsonFragment(['name' => $user3->name]);
    }

    /*
    Test that the login returns a user token.
    */
    /**
     * Test that the login returns a user token.
     *
     * @return void
     */
    public function test_login_returns_token()
    {
        // Create a user with the factory and password hash directly in the model
        $user = User::factory()->create([
            'email' => 'a1@email.pt',
            'password' => Hash::make('password'),  // Ensure password is hashed
        ]);

        $data = [
            'email' => 'a1@email.pt',
            'password' => 'password',
        ];

        // Make the POST request to the login endpoint
        $response = $this->postJson('/api/login', $data);

        // Assert that the response status is 200 and the structure contains the 'token'
        $response->assertStatus(200)
            ->assertJsonStructure(['remember_token']) // Expecting a 'token' key in the response
            ->assertJson(fn($json) => $json->has('remember_token')->etc());

        // Optionally, you can assert that the token is a string and has the expected length
        $token = $response->json('remember_token');
        $this->assertIsString($token);
        $this->assertGreaterThan(10, strlen($token)); // Assuming tokens are long enough

        // Check that the user exists in the database (check the user's email)
        $this->assertDatabaseHas('users', [
            'email' => 'a1@email.pt',
        ]);
    }

}
