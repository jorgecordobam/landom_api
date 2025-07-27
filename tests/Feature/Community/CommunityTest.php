<?php

namespace Tests\Feature\Community;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommunityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_create_post()
    {
        $user = $this->authenticateAs('constructor');

        $postData = [
            'title' => 'Project Update: Downtown Renovation',
            'content' => 'Excited to share progress on our latest project...',
            'type' => 'project_update',
            'is_public' => true
        ];

        $response = $this->postJson('/api/community/posts', $postData);

        $this->assertApiResponse($response, 201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'post' => [
                    'id',
                    'title',
                    'content',
                    'type',
                    'is_public',
                    'author',
                    'created_at',
                    'likes_count',
                    'comments_count'
                ]
            ]
        ]);

        $this->assertDatabaseHas('posts', [
            'title' => 'Project Update: Downtown Renovation',
            'author_id' => $user->id,
            'type' => 'project_update'
        ]);
    }

    /** @test */
    public function post_creation_requires_mandatory_fields()
    {
        $this->authenticateAs();

        $response = $this->postJson('/api/community/posts', []);

        $this->assertValidationError($response);
        $response->assertJsonValidationErrors(['title', 'content', 'type']);
    }

    /** @test */
    public function post_validates_type()
    {
        $this->authenticateAs();

        $postData = [
            'title' => 'Test Post',
            'content' => 'Test content',
            'type' => 'invalid_type'
        ];

        $response = $this->postJson('/api/community/posts', $postData);

        $this->assertValidationError($response, 'type');
    }

    /** @test */
    public function user_can_view_public_posts()
    {
        $this->authenticateAs();

        // Create public and private posts
        Post::factory(3)->create(['is_public' => true]);
        Post::factory(2)->create(['is_public' => false]);

        $response = $this->getJson('/api/community/posts');

        $this->assertApiResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'posts' => [
                    '*' => [
                        'id',
                        'title',
                        'content',
                        'type',
                        'author',
                        'created_at',
                        'likes_count',
                        'comments_count',
                        'user_has_liked'
                    ]
                ],
                'pagination'
            ]
        ]);

        // Should only return public posts
        $this->assertCount(3, $response->json('data.posts'));
    }

    /** @test */
    public function user_can_view_post_details()
    {
        $user = $this->authenticateAs();
        $post = Post::factory()->create(['is_public' => true]);

        $response = $this->getJson("/api/community/posts/{$post->id}");

        $this->assertApiResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'post' => [
                    'id',
                    'title',
                    'content',
                    'type',
                    'author',
                    'created_at',
                    'likes_count',
                    'comments_count',
                    'user_has_liked'
                ],
                'recent_comments' => [
                    '*' => [
                        'id',
                        'content',
                        'author',
                        'created_at'
                    ]
                ]
            ]
        ]);
    }

    /** @test */
    public function user_cannot_view_private_post()
    {
        $author = User::factory()->create();
        $viewer = $this->authenticateAs();
        
        $post = Post::factory()->create([
            'author_id' => $author->id,
            'is_public' => false
        ]);

        $response = $this->getJson("/api/community/posts/{$post->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function author_can_view_own_private_post()
    {
        $author = $this->authenticateAs();
        
        $post = Post::factory()->create([
            'author_id' => $author->id,
            'is_public' => false
        ]);

        $response = $this->getJson("/api/community/posts/{$post->id}");

        $this->assertApiResponse($response);
    }

    /** @test */
    public function user_can_update_own_post()
    {
        $author = $this->authenticateAs();
        
        $post = Post::factory()->create([
            'author_id' => $author->id,
            'title' => 'Original Title'
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'content' => 'Updated content'
        ];

        $response = $this->putJson("/api/community/posts/{$post->id}", $updateData);

        $this->assertApiResponse($response);
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Title'
        ]);
    }

    /** @test */
    public function user_cannot_update_others_post()
    {
        $author = User::factory()->create();
        $otherUser = $this->authenticateAs();
        
        $post = Post::factory()->create(['author_id' => $author->id]);

        $updateData = [
            'title' => 'Hacked Title'
        ];

        $response = $this->putJson("/api/community/posts/{$post->id}", $updateData);

        $response->assertStatus(403);
    }

    /** @test */
    public function user_can_delete_own_post()
    {
        $author = $this->authenticateAs();
        
        $post = Post::factory()->create(['author_id' => $author->id]);

        $response = $this->deleteJson("/api/community/posts/{$post->id}");

        $this->assertApiResponse($response);
        $this->assertDatabaseMissing('posts', [
            'id' => $post->id
        ]);
    }

    /** @test */
    public function user_can_like_post()
    {
        $user = $this->authenticateAs();
        $post = Post::factory()->create(['is_public' => true]);

        $response = $this->postJson("/api/community/posts/{$post->id}/toggle-like");

        $this->assertApiResponse($response);
        $response->assertJson([
            'success' => true,
            'message' => 'Post liked successfully'
        ]);

        $this->assertDatabaseHas('post_likes', [
            'post_id' => $post->id,
            'user_id' => $user->id
        ]);
    }

    /** @test */
    public function user_can_unlike_post()
    {
        $user = $this->authenticateAs();
        $post = Post::factory()->create(['is_public' => true]);

        // First like the post
        $post->likes()->create(['user_id' => $user->id]);

        $response = $this->postJson("/api/community/posts/{$post->id}/toggle-like");

        $this->assertApiResponse($response);
        $response->assertJson([
            'success' => true,
            'message' => 'Post unliked successfully'
        ]);

        $this->assertDatabaseMissing('post_likes', [
            'post_id' => $post->id,
            'user_id' => $user->id
        ]);
    }

    /** @test */
    public function user_can_comment_on_post()
    {
        $user = $this->authenticateAs();
        $post = Post::factory()->create(['is_public' => true]);

        $commentData = [
            'content' => 'Great project! Looking forward to seeing the results.'
        ];

        $response = $this->postJson("/api/community/posts/{$post->id}/comments", $commentData);

        $this->assertApiResponse($response, 201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'comment' => [
                    'id',
                    'content',
                    'author',
                    'created_at',
                    'likes_count'
                ]
            ]
        ]);

        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'author_id' => $user->id,
            'content' => 'Great project! Looking forward to seeing the results.'
        ]);
    }

    /** @test */
    public function comment_requires_content()
    {
        $this->authenticateAs();
        $post = Post::factory()->create(['is_public' => true]);

        $response = $this->postJson("/api/community/posts/{$post->id}/comments", []);

        $this->assertValidationError($response, 'content');
    }

    /** @test */
    public function user_can_view_post_comments()
    {
        $this->authenticateAs();
        $post = Post::factory()->create(['is_public' => true]);
        
        Comment::factory(5)->create(['post_id' => $post->id]);

        $response = $this->getJson("/api/community/posts/{$post->id}/comments");

        $this->assertApiResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'comments' => [
                    '*' => [
                        'id',
                        'content',
                        'author',
                        'created_at',
                        'likes_count',
                        'user_has_liked'
                    ]
                ],
                'pagination'
            ]
        ]);

        $this->assertCount(5, $response->json('data.comments'));
    }

    /** @test */
    public function user_can_update_own_comment()
    {
        $author = $this->authenticateAs();
        $post = Post::factory()->create(['is_public' => true]);
        
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'author_id' => $author->id,
            'content' => 'Original comment'
        ]);

        $updateData = [
            'content' => 'Updated comment content'
        ];

        $response = $this->putJson("/api/community/posts/{$post->id}/comments/{$comment->id}", $updateData);

        $this->assertApiResponse($response);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Updated comment content'
        ]);
    }

    /** @test */
    public function user_can_delete_own_comment()
    {
        $author = $this->authenticateAs();
        $post = Post::factory()->create(['is_public' => true]);
        
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'author_id' => $author->id
        ]);

        $response = $this->deleteJson("/api/community/posts/{$post->id}/comments/{$comment->id}");

        $this->assertApiResponse($response);
        $this->assertDatabaseMissing('comments', [
            'id' => $comment->id
        ]);
    }

    /** @test */
    public function user_can_like_comment()
    {
        $user = $this->authenticateAs();
        $post = Post::factory()->create(['is_public' => true]);
        $comment = Comment::factory()->create(['post_id' => $post->id]);

        $response = $this->postJson("/api/community/posts/{$post->id}/comments/{$comment->id}/toggle-like");

        $this->assertApiResponse($response);
        $this->assertDatabaseHas('comment_likes', [
            'comment_id' => $comment->id,
            'user_id' => $user->id
        ]);
    }

    /** @test */
    public function posts_can_be_filtered_by_type()
    {
        $this->authenticateAs();

        Post::factory(2)->create(['type' => 'project_update', 'is_public' => true]);
        Post::factory(3)->create(['type' => 'investment_opportunity', 'is_public' => true]);

        $response = $this->getJson('/api/community/posts?type=project_update');

        $this->assertApiResponse($response);
        $this->assertCount(2, $response->json('data.posts'));
    }

    /** @test */
    public function posts_can_be_searched()
    {
        $this->authenticateAs();

        Post::factory()->create([
            'title' => 'Renovation Project Downtown',
            'content' => 'Working on a major renovation...',
            'is_public' => true
        ]);

        Post::factory()->create([
            'title' => 'New Construction Site',
            'content' => 'Building from ground up...',
            'is_public' => true
        ]);

        $response = $this->getJson('/api/community/posts?search=renovation');

        $this->assertApiResponse($response);
        $this->assertCount(1, $response->json('data.posts'));
        $this->assertStringContains('Renovation', $response->json('data.posts.0.title'));
    }
}
