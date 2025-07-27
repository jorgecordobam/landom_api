<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_verified' => true,
            'status' => 'active'
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $this->assertApiResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => [
                    'id',
                    'name',
                    'email',
                    'role',
                    'is_verified',
                    'status'
                ],
                'token'
            ]
        ]);
        
        $this->assertTrue($response->json('success'));
        $this->assertEquals($user->id, $response->json('data.user.id'));
        $this->assertNotEmpty($response->json('data.token'));
    }

    /** @test */
    public function user_cannot_login_with_invalid_email()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'message' => 'Invalid credentials'
        ]);
    }

    /** @test */
    public function user_cannot_login_with_invalid_password()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('correctpassword'),
            'is_verified' => true,
            'status' => 'active'
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'message' => 'Invalid credentials'
        ]);
    }

    /** @test */
    public function unverified_user_cannot_login()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_verified' => false,
            'status' => 'active'
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'message' => 'Email not verified'
        ]);
    }

    /** @test */
    public function inactive_user_cannot_login()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_verified' => true,
            'status' => 'inactive'
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'message' => 'Account is inactive'
        ]);
    }

    /** @test */
    public function login_requires_email_and_password()
    {
        // Missing email
        $response = $this->postJson('/api/auth/login', [
            'password' => 'password123'
        ]);
        $this->assertValidationError($response, 'email');

        // Missing password
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com'
        ]);
        $this->assertValidationError($response, 'password');

        // Missing both
        $response = $this->postJson('/api/auth/login', []);
        $this->assertValidationError($response);
    }

    /** @test */
    public function login_validates_email_format()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'invalid-email',
            'password' => 'password123'
        ]);

        $this->assertValidationError($response, 'email');
    }

    /** @test */
    public function authenticated_user_can_logout()
    {
        $user = $this->authenticateAs();

        $response = $this->postJson('/api/auth/logout');

        $this->assertApiResponse($response);
        $response->assertJson([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);

        // Verify token is revoked
        $response = $this->getJson('/api/auth/me');
        $response->assertStatus(401);
    }

    /** @test */
    public function unauthenticated_user_cannot_logout()
    {
        $response = $this->postJson('/api/auth/logout');
        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_user_can_get_profile()
    {
        $user = $this->authenticateAs();

        $response = $this->getJson('/api/auth/me');

        $this->assertApiResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'name',
                'email',
                'role',
                'is_verified',
                'status'
            ]
        ]);
        
        $this->assertEquals($user->id, $response->json('data.id'));
    }
}
