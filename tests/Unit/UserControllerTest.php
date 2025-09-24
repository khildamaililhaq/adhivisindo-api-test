<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test getting list of users with pagination
     */
    public function test_index_returns_paginated_users()
    {
        User::factory()->count(15)->create();

        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => ['id', 'name', 'email']
                     ],
                     'current_page',
                     'per_page',
                     'total'
                 ])
                 ->assertJsonCount(10, 'data'); // Default pagination
    }

    /**
     * Test searching users
     */
    public function test_index_with_search()
    {
        User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

        $response = $this->getJson('/api/users?search=John');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data')
                 ->assertJsonFragment(['name' => 'John Doe']);
    }

    /**
     * Test filtering verified users
     */
    public function test_index_filter_verified()
    {
        User::factory()->create(['email_verified_at' => now()]);
        User::factory()->create(['email_verified_at' => null]);

        $response = $this->getJson('/api/users?filter=verified');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data');
    }

    /**
     * Test filtering unverified users
     */
    public function test_index_filter_unverified()
    {
        User::factory()->create(['email_verified_at' => now()]);
        User::factory()->create(['email_verified_at' => null]);

        $response = $this->getJson('/api/users?filter=unverified');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data');
    }

    /**
     * Test creating a user
     */
    public function test_store_creates_user()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(201)
                 ->assertJsonStructure(['id', 'name', 'email']);

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    /**
     * Test creating user with invalid data
     */
    public function test_store_validation_fails()
    {
        $userData = [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(422)
                 ->assertJsonStructure(['errors']);
    }

    /**
     * Test showing a user
     */
    public function test_show_returns_user()
    {
        $user = User::factory()->create();

        $response = $this->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $user->id,
                     'name' => $user->name,
                     'email' => $user->email,
                 ]);
    }

    /**
     * Test showing non-existent user
     */
    public function test_show_user_not_found()
    {
        $response = $this->getJson('/api/users/999');

        $response->assertStatus(404);
    }

    /**
     * Test updating a user
     */
    public function test_update_user()
    {
        $user = User::factory()->create();

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ];

        $response = $this->putJson("/api/users/{$user->id}", $updateData);

        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $user->id,
                     'name' => 'Updated Name',
                     'email' => 'updated@example.com',
                 ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    /**
     * Test updating user with password
     */
    public function test_update_user_with_password()
    {
        $user = User::factory()->create();

        $updateData = [
            'name' => 'Updated Name',
            'password' => 'newpassword123',
        ];

        $response = $this->putJson("/api/users/{$user->id}", $updateData);

        $response->assertStatus(200);

        $updatedUser = User::find($user->id);
        $this->assertTrue(Hash::check('newpassword123', $updatedUser->password));
    }

    /**
     * Test updating non-existent user
     */
    public function test_update_user_not_found()
    {
        $updateData = ['name' => 'Updated Name'];

        $response = $this->putJson('/api/users/999', $updateData);

        $response->assertStatus(404);
    }

    /**
     * Test deleting a user
     */
    public function test_destroy_deletes_user()
    {
        $user = User::factory()->create();

        $response = $this->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    /**
     * Test deleting non-existent user
     */
    public function test_destroy_user_not_found()
    {
        $response = $this->deleteJson('/api/users/999');

        $response->assertStatus(404);
    }
}