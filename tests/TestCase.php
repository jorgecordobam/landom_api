<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Create and authenticate a user with specified role
     */
    protected function authenticateAs($role = 'general_user', $attributes = [])
    {
        $user = User::factory()->create(array_merge([
            'role' => $role,
            'is_verified' => true,
            'status' => 'active'
        ], $attributes));

        Sanctum::actingAs($user);
        
        return $user;
    }

    /**
     * Create a guest user (not authenticated)
     */
    protected function actAsGuest()
    {
        auth()->logout();
        return $this;
    }

    /**
     * Assert that response has correct API structure
     */
    protected function assertApiResponse($response, $status = 200)
    {
        $response->assertStatus($status);
        $response->assertJsonStructure([
            'success',
            'message',
            'data'
        ]);
    }

    /**
     * Assert validation error response
     */
    protected function assertValidationError($response, $field = null)
    {
        $response->assertStatus(422);
        $response->assertJsonStructure([
            'success',
            'message',
            'errors'
        ]);

        if ($field) {
            $response->assertJsonValidationErrors($field);
        }
    }
}
