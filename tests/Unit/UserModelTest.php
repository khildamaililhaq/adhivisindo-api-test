<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user fillable attributes
     */
    public function test_user_fillable_attributes()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'api_token' => 'test-token',
        ]);

        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals('test-token', $user->api_token);
    }

    /**
     * Test user hidden attributes
     */
    public function test_user_hidden_attributes()
    {
        $user = User::factory()->create();

        $userArray = $user->toArray();

        $this->assertArrayNotHasKey('password', $userArray);
        $this->assertArrayNotHasKey('remember_token', $userArray);
    }

    /**
     * Test generate API token
     */
    public function test_generate_api_token()
    {
        $user = User::factory()->create();

        $token = $user->generateApiToken();

        $this->assertNotNull($token);
        $this->assertEquals(80, strlen($token)); // bin2hex(random_bytes(40)) = 80 chars
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'api_token' => $token,
        ]);
    }

    /**
     * Test casts
     */
    public function test_casts()
    {
        $user = User::factory()->create([
            'email_verified_at' => '2023-01-01 00:00:00',
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $user->email_verified_at);
    }
}