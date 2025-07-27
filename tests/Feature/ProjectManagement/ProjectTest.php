<?php

namespace Tests\Feature\ProjectManagement;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function constructor_can_create_project()
    {
        $constructor = $this->authenticateAs('constructor', [
            'company_name' => 'Test Construction Co.'
        ]);

        $projectData = [
            'name' => 'Test Project',
            'description' => 'A test construction project',
            'location' => '123 Test Street, Test City',
            'estimated_budget' => 100000,
            'estimated_duration' => 120,
            'project_type' => 'fix_and_flip',
            'status' => 'planning'
        ];

        $response = $this->postJson('/api/projects', $projectData);

        $this->assertApiResponse($response, 201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'name',
                'description',
                'location',
                'estimated_budget',
                'estimated_duration',
                'project_type',
                'status',
                'created_by'
            ]
        ]);

        $this->assertDatabaseHas('projects', [
            'name' => 'Test Project',
            'created_by' => $constructor->id,
            'status' => 'planning'
        ]);
    }

    /** @test */
    public function general_user_cannot_create_project()
    {
        $this->authenticateAs('general_user');

        $projectData = [
            'name' => 'Test Project',
            'description' => 'A test construction project',
            'location' => '123 Test Street, Test City',
            'estimated_budget' => 100000,
            'estimated_duration' => 120,
            'project_type' => 'fix_and_flip'
        ];

        $response = $this->postJson('/api/projects', $projectData);

        $response->assertStatus(403);
    }

    /** @test */
    public function project_creation_requires_mandatory_fields()
    {
        $this->authenticateAs('constructor');

        $response = $this->postJson('/api/projects', []);

        $this->assertValidationError($response);
        $response->assertJsonValidationErrors([
            'name', 'description', 'location', 'estimated_budget', 'project_type'
        ]);
    }

    /** @test */
    public function project_validates_positive_budget()
    {
        $this->authenticateAs('constructor');

        $projectData = [
            'name' => 'Test Project',
            'description' => 'A test construction project',
            'location' => '123 Test Street, Test City',
            'estimated_budget' => -1000,
            'project_type' => 'fix_and_flip'
        ];

        $response = $this->postJson('/api/projects', $projectData);

        $this->assertValidationError($response, 'estimated_budget');
    }

    /** @test */
    public function project_validates_project_type()
    {
        $this->authenticateAs('constructor');

        $projectData = [
            'name' => 'Test Project',
            'description' => 'A test construction project',
            'location' => '123 Test Street, Test City',
            'estimated_budget' => 100000,
            'project_type' => 'invalid_type'
        ];

        $response = $this->postJson('/api/projects', $projectData);

        $this->assertValidationError($response, 'project_type');
    }

    /** @test */
    public function user_can_view_project_details()
    {
        $constructor = $this->authenticateAs('constructor');
        
        $project = Project::factory()->create([
            'created_by' => $constructor->id,
            'name' => 'Test Project',
            'status' => 'active'
        ]);

        $response = $this->getJson("/api/projects/{$project->id}");

        $this->assertApiResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'name',
                'description',
                'location',
                'estimated_budget',
                'status',
                'created_by',
                'progress_percentage'
            ]
        ]);

        $this->assertEquals($project->id, $response->json('data.id'));
    }

    /** @test */
    public function user_cannot_view_nonexistent_project()
    {
        $this->authenticateAs();

        $response = $this->getJson('/api/projects/99999');

        $response->assertStatus(404);
    }

    /** @test */
    public function constructor_can_update_own_project()
    {
        $constructor = $this->authenticateAs('constructor');
        
        $project = Project::factory()->create([
            'created_by' => $constructor->id,
            'name' => 'Original Name'
        ]);

        $updateData = [
            'name' => 'Updated Project Name',
            'description' => 'Updated description'
        ];

        $response = $this->putJson("/api/projects/{$project->id}", $updateData);

        $this->assertApiResponse($response);
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Updated Project Name'
        ]);
    }

    /** @test */
    public function user_cannot_update_others_project()
    {
        $owner = User::factory()->create(['role' => 'constructor']);
        $otherUser = $this->authenticateAs('constructor');
        
        $project = Project::factory()->create([
            'created_by' => $owner->id
        ]);

        $updateData = [
            'name' => 'Hacked Name'
        ];

        $response = $this->putJson("/api/projects/{$project->id}", $updateData);

        $response->assertStatus(403);
    }

    /** @test */
    public function project_admin_can_update_any_project()
    {
        $owner = User::factory()->create(['role' => 'constructor']);
        $admin = $this->authenticateAs('project_admin');
        
        $project = Project::factory()->create([
            'created_by' => $owner->id,
            'name' => 'Original Name'
        ]);

        $updateData = [
            'name' => 'Admin Updated Name'
        ];

        $response = $this->putJson("/api/projects/{$project->id}", $updateData);

        $this->assertApiResponse($response);
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Admin Updated Name'
        ]);
    }

    /** @test */
    public function constructor_can_delete_own_project()
    {
        $constructor = $this->authenticateAs('constructor');
        
        $project = Project::factory()->create([
            'created_by' => $constructor->id
        ]);

        $response = $this->deleteJson("/api/projects/{$project->id}");

        $this->assertApiResponse($response);
        $this->assertDatabaseMissing('projects', [
            'id' => $project->id
        ]);
    }

    /** @test */
    public function user_can_view_project_list()
    {
        $this->authenticateAs();

        // Create some projects
        Project::factory(3)->create(['status' => 'active']);
        Project::factory(2)->create(['status' => 'completed']);

        $response = $this->getJson('/api/projects');

        $this->assertApiResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'projects' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'location',
                        'estimated_budget',
                        'status',
                        'progress_percentage'
                    ]
                ],
                'pagination'
            ]
        ]);

        $this->assertCount(5, $response->json('data.projects'));
    }

    /** @test */
    public function user_can_filter_projects_by_status()
    {
        $this->authenticateAs();

        Project::factory(2)->create(['status' => 'active']);
        Project::factory(3)->create(['status' => 'completed']);

        $response = $this->getJson('/api/projects?status=active');

        $this->assertApiResponse($response);
        $this->assertCount(2, $response->json('data.projects'));
    }

    /** @test */
    public function user_can_get_project_progress()
    {
        $constructor = $this->authenticateAs('constructor');
        
        $project = Project::factory()->create([
            'created_by' => $constructor->id
        ]);

        $response = $this->getJson("/api/projects/{$project->id}/progress");

        $this->assertApiResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'project_id',
                'progress_percentage',
                'completed_tasks',
                'total_tasks',
                'phases' => [
                    '*' => [
                        'name',
                        'progress',
                        'tasks_completed',
                        'tasks_total'
                    ]
                ]
            ]
        ]);
    }
}
