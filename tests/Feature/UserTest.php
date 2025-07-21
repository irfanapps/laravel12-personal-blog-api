<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    //use RefreshDatabase;

    /**
     * Test user registration success
     */
    public function test_user_registration_successful()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123'
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ])
            ->assertJson([
                'status' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'user' => [
                        'name' => 'Test User',
                        'email' => 'test@example.com'
                    ]
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com'
        ]);
    }

    /**
     * Test user registration validation errors
     */
    public function test_user_registration_validation_errors()
    {
        // Test empty fields
        $response = $this->postJson('/api/auth/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);

        // Test password confirmation mismatch
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'DifferentPassword'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);

        // Test invalid email
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'not-an-email',
            'password' => 'Password123',
            'password_confirmation' => 'Password123'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test user login successful
     */
    public function test_user_login_successful()
    {
        // Create test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('Password123')
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'Password123'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'created_at',
                        'updated_at'
                    ],
                    'token'
                ]
            ])
            ->assertJson([
                'status' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'email' => 'test@example.com'
                    ]
                ]
            ]);
    }

    /**
     * Test user login with invalid credentials
     */
    public function test_user_login_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('Password123')
        ]);

        // Wrong password
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'WrongPassword'
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => false,
                'message' => 'Invalid credentials'
            ]);

        // Non-existent email
        $response = $this->postJson('/api/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'Password123'
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test user logout successful
     */
    public function test_user_logout_successful()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Successfully logged out'
            ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id
        ]);
    }

    /**
     * Test user logout unauthorized
     */
    public function test_user_logout_unauthorized()
    {
        // Without token
        $response = $this->postJson('/api/auth/logout');
        $response->assertStatus(401);

        // With invalid token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalidtoken'
        ])->postJson('/api/auth/logout');

        $response->assertStatus(401);
    }


    /**
     * Test unauthenticated user cannot get profile
     */
    public function test_get_profile_unauthenticated()
    {
        $response = $this->getJson('/api/auth/me');
        $response->assertStatus(401);
    }
}
