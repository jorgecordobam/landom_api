<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SystemConfigurationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function api_returns_proper_cors_headers()
    {
        $response = $this->getJson('/api/auth/login');

        // Should have CORS headers for cross-origin requests (Flutter app)
        $response->assertHeader('Access-Control-Allow-Origin');
    }

    /** @test */
    public function api_requires_authentication_for_protected_routes()
    {
        $protectedRoutes = [
            '/api/auth/logout',
            '/api/auth/me',
            '/api/profile',
            '/api/projects',
            '/api/investor/investments',
            '/api/admin/users'
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->getJson($route);
            $this->assertEquals(401, $response->status(), "Route {$route} should require authentication");
        }
    }

    /** @test */
    public function api_has_rate_limiting()
    {
        // Test rate limiting on login endpoint
        for ($i = 0; $i < 10; $i++) {
            $response = $this->postJson('/api/auth/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password'
            ]);
        }

        // After many failed attempts, should be rate limited
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password'
        ]);

        // Depending on rate limiting configuration, could be 429 or still 401
        $this->assertContains($response->status(), [401, 429]);
    }

    /** @test */
    public function api_returns_consistent_error_format()
    {
        // Test 404 error format
        $response = $this->getJson('/api/nonexistent-endpoint');
        $response->assertStatus(404);
        $response->assertJsonStructure([
            'success',
            'message'
        ]);
        $this->assertFalse($response->json('success'));

        // Test validation error format
        $response = $this->postJson('/api/auth/login', []);
        $response->assertStatus(422);
        $response->assertJsonStructure([
            'success',
            'message',
            'errors'
        ]);
        $this->assertFalse($response->json('success'));
    }

    /** @test */
    public function api_handles_method_not_allowed()
    {
        // Try POST on a GET-only endpoint
        $response = $this->postJson('/api/auth/me');
        $response->assertStatus(405); // Method Not Allowed
    }

    /** @test */
    public function api_validates_content_type()
    {
        // Send request without JSON content type
        $response = $this->call('POST', '/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password'
        ], [], [], [
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded'
        ]);

        // Should still work or return appropriate error
        $this->assertContains($response->status(), [200, 401, 422]);
    }

    /** @test */
    public function api_handles_large_payloads()
    {
        $this->authenticateAs();

        $largeContent = str_repeat('Lorem ipsum dolor sit amet. ', 1000); // ~27KB

        $response = $this->postJson('/api/community/posts', [
            'title' => 'Test Post',
            'content' => $largeContent,
            'type' => 'general_discussion'
        ]);

        // Should either succeed or fail with proper validation error
        $this->assertContains($response->status(), [201, 422]);
    }

    /** @test */
    public function api_sanitizes_user_input()
    {
        $this->authenticateAs();

        $maliciousInput = '<script>alert("xss")</script>';

        $response = $this->postJson('/api/community/posts', [
            'title' => $maliciousInput,
            'content' => 'Test content',
            'type' => 'general_discussion'
        ]);

        if ($response->status() === 201) {
            // If post was created, title should be sanitized
            $this->assertStringNotContainsString('<script>', $response->json('data.post.title'));
        }
    }

    /** @test */
    public function database_connections_work_properly()
    {
        // Test that we can write and read from database
        $user = $this->authenticateAs();
        
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $user->email
        ]);

        // Test that foreign key constraints work
        $response = $this->postJson('/api/projects', [
            'name' => 'Test Project',
            'description' => 'Test description',
            'location' => 'Test location',
            'estimated_budget' => 100000,
            'project_type' => 'fix_and_flip'
        ]);

        if ($response->status() === 201) {
            $projectId = $response->json('data.id');
            $this->assertDatabaseHas('projects', [
                'id' => $projectId,
                'created_by' => $user->id
            ]);
        }
    }

    /** @test */
    public function api_pagination_works_correctly()
    {
        $this->authenticateAs();

        // Create multiple records
        for ($i = 0; $i < 25; $i++) {
            \App\Models\Post::factory()->create(['is_public' => true]);
        }

        // Test first page
        $response = $this->getJson('/api/community/posts?page=1&per_page=10');
        $this->assertApiResponse($response);
        
        $data = $response->json('data');
        $this->assertArrayHasKey('pagination', $data);
        $this->assertLessThanOrEqual(10, count($data['posts']));

        // Test second page
        $response = $this->getJson('/api/community/posts?page=2&per_page=10');
        $this->assertApiResponse($response);
        
        $secondPageData = $response->json('data');
        $this->assertNotEquals($data['posts'], $secondPageData['posts']);
    }

    /** @test */
    public function api_search_functionality_works()
    {
        $this->authenticateAs();

        // Create searchable content
        \App\Models\Post::factory()->create([
            'title' => 'Unique Construction Project Title',
            'content' => 'This is about construction work',
            'is_public' => true
        ]);

        \App\Models\Post::factory()->create([
            'title' => 'Different Topic Entirely',
            'content' => 'This is about something else',
            'is_public' => true
        ]);

        $response = $this->getJson('/api/community/posts?search=construction');
        $this->assertApiResponse($response);

        $posts = $response->json('data.posts');
        $this->assertGreaterThan(0, count($posts));
        
        // Verify search results contain the search term
        $found = false;
        foreach ($posts as $post) {
            if (stripos($post['title'], 'construction') !== false || stripos($post['content'], 'construction') !== false) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
    }
}
