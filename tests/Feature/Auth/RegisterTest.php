<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_register_with_valid_data()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'general_user',
            'phone' => '+1234567890',
            'company_name' => 'Test Company'
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $this->assertApiResponse($response, 201);
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
                ]
            ]
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
            'role' => 'general_user',
            'is_verified' => false,
            'status' => 'pending'
        ]);

        $this->assertTrue($response->json('success'));
        $this->assertFalse($response->json('data.user.is_verified'));
    }

    /** @test */
    public function registration_requires_all_mandatory_fields()
    {
        $response = $this->postJson('/api/auth/register', []);

        $this->assertValidationError($response);
        $response->assertJsonValidationErrors(['name', 'email', 'password', 'role']);
    }

    /** @test */
    public function registration_validates_email_format()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'general_user'
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $this->assertValidationError($response, 'email');
    }

    /** @test */
    public function registration_validates_unique_email()
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $userData = [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'general_user'
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $this->assertValidationError($response, 'email');
    }

    /** @test */
    public function registration_validates_password_confirmation()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword',
            'role' => 'general_user'
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $this->assertValidationError($response, 'password');
    }

    /** @test */
    public function registration_validates_password_minimum_length()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '123',
            'password_confirmation' => '123',
            'role' => 'general_user'
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $this->assertValidationError($response, 'password');
    }

    /** @test */
    public function registration_validates_valid_role()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'invalid_role'
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $this->assertValidationError($response, 'role');
    }

    /** @test */
    public function constructor_registration_requires_company_name()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'constructor'
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $this->assertValidationError($response, 'company_name');
    }

    /** @test */
    public function investor_registration_creates_pending_status()
    {
        $userData = [
            'name' => 'Test Investor',
            'email' => 'investor@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'investor',
            'phone' => '+1234567890'
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $this->assertApiResponse($response, 201);
        $this->assertDatabaseHas('users', [
            'email' => 'investor@example.com',
            'role' => 'investor',
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function registration_validates_phone_format()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'general_user',
            'phone' => 'invalid-phone'
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $this->assertValidationError($response, 'phone');
    }

    /** @test */
    public function registration_encrypts_password()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'general_user'
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $this->assertApiResponse($response, 201);
        
        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotEquals('password123', $user->password);
        $this->assertTrue(Hash::check('password123', $user->password));
    }
}
