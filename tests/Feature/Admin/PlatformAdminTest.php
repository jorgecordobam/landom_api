<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PlatformAdminTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_view_all_users()
    {
        $admin = $this->authenticateAs('platform_admin');

        // Create various users
        User::factory(5)->create(['role' => 'general_user']);
        User::factory(3)->create(['role' => 'constructor']);
        User::factory(2)->create(['role' => 'investor']);

        $response = $this->getJson('/api/admin/users');

        $this->assertApiResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'users' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'role',
                        'status',
                        'is_verified',
                        'created_at'
                    ]
                ],
                'pagination',
                'statistics' => [
                    'total_users',
                    'verified_users',
                    'pending_verification',
                    'by_role'
                ]
            ]
        ]);

        // Should return 10 users + admin = 11 total
        $this->assertCount(11, $response->json('data.users'));
    }

    /** @test */
    public function non_admin_cannot_access_admin_routes()
    {
        $this->authenticateAs('general_user');

        $response = $this->getJson('/api/admin/users');

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_update_user_status()
    {
        $admin = $this->authenticateAs('platform_admin');
        $user = User::factory()->create(['status' => 'pending']);

        $response = $this->putJson("/api/admin/users/{$user->id}/status", [
            'status' => 'verified',
            'reason' => 'Documentation approved'
        ]);

        $this->assertApiResponse($response);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'verified'
        ]);
    }

    /** @test */
    public function admin_can_suspend_user()
    {
        $admin = $this->authenticateAs('platform_admin');
        $user = User::factory()->create(['status' => 'active']);

        $response = $this->putJson("/api/admin/users/{$user->id}/status", [
            'status' => 'suspended',
            'reason' => 'Policy violation'
        ]);

        $this->assertApiResponse($response);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'suspended'
        ]);
    }

    /** @test */
    public function admin_can_delete_user()
    {
        $admin = $this->authenticateAs('platform_admin');
        $user = User::factory()->create();

        $response = $this->deleteJson("/api/admin/users/{$user->id}");

        $this->assertApiResponse($response);
        $this->assertDatabaseMissing('users', [
            'id' => $user->id
        ]);
    }

    /** @test */
    public function admin_cannot_delete_themselves()
    {
        $admin = $this->authenticateAs('platform_admin');

        $response = $this->deleteJson("/api/admin/users/{$admin->id}");

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Cannot delete your own account'
        ]);
    }

    /** @test */
    public function admin_can_view_company_verification_requests()
    {
        $admin = $this->authenticateAs('platform_admin');

        // Create companies with verification requests
        $companies = Company::factory(3)->create(['verification_status' => 'pending']);

        $response = $this->getJson('/api/admin/companies/pending');

        $this->assertApiResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'companies' => [
                    '*' => [
                        'id',
                        'name',
                        'owner',
                        'registration_documents',
                        'verification_status',
                        'submitted_at'
                    ]
                ]
            ]
        ]);

        $this->assertCount(3, $response->json('data.companies'));
    }

    /** @test */
    public function admin_can_verify_company()
    {
        $admin = $this->authenticateAs('platform_admin');
        $company = Company::factory()->create(['verification_status' => 'pending']);

        $response = $this->postJson("/api/admin/companies/{$company->id}/verify", [
            'notes' => 'All documents verified successfully'
        ]);

        $this->assertApiResponse($response);
        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'verification_status' => 'verified'
        ]);
    }

    /** @test */
    public function admin_can_reject_company_verification()
    {
        $admin = $this->authenticateAs('platform_admin');
        $company = Company::factory()->create(['verification_status' => 'pending']);

        $response = $this->postJson("/api/admin/companies/{$company->id}/reject", [
            'reason' => 'Incomplete documentation',
            'notes' => 'Missing license certificate'
        ]);

        $this->assertApiResponse($response);
        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'verification_status' => 'rejected'
        ]);
    }

    /** @test */
    public function admin_can_view_project_oversight()
    {
        $admin = $this->authenticateAs('platform_admin');

        Project::factory(5)->create(['status' => 'active']);
        Project::factory(3)->create(['status' => 'completed']);
        Project::factory(2)->create(['status' => 'seeking_investment']);

        $response = $this->getJson('/api/admin/projects');

        $this->assertApiResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'projects' => [
                    '*' => [
                        'id',
                        'name',
                        'creator',
                        'status',
                        'estimated_budget',
                        'progress_percentage',
                        'created_at'
                    ]
                ],
                'pagination'
            ]
        ]);

        $this->assertCount(10, $response->json('data.projects'));
    }

    /** @test */
    public function admin_can_view_project_statistics()
    {
        $admin = $this->authenticateAs('platform_admin');

        Project::factory(10)->create(['status' => 'active']);
        Project::factory(5)->create(['status' => 'completed']);
        Project::factory(3)->create(['status' => 'seeking_investment']);

        $response = $this->getJson('/api/admin/projects/statistics');

        $this->assertApiResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'total_projects',
                'active_projects',
                'completed_projects',
                'seeking_investment',
                'total_investment',
                'average_project_value',
                'success_rate',
                'monthly_growth'
            ]
        ]);

        $data = $response->json('data');
        $this->assertEquals(18, $data['total_projects']);
        $this->assertEquals(10, $data['active_projects']);
        $this->assertEquals(5, $data['completed_projects']);
    }

    /** @test */
    public function admin_can_update_project_status()
    {
        $admin = $this->authenticateAs('platform_admin');
        $project = Project::factory()->create(['status' => 'active']);

        $response = $this->putJson("/api/admin/projects/{$project->id}/status", [
            'status' => 'suspended',
            'reason' => 'Compliance issue detected'
        ]);

        $this->assertApiResponse($response);
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'status' => 'suspended'
        ]);
    }

    /** @test */
    public function admin_can_view_system_settings()
    {
        $admin = $this->authenticateAs('platform_admin');

        $response = $this->getJson('/api/admin/settings');

        $this->assertApiResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'platform_settings' => [
                    'maintenance_mode',
                    'registration_enabled',
                    'investment_enabled',
                    'minimum_investment_amount',
                    'platform_fee_percentage'
                ],
                'notification_settings',
                'security_settings'
            ]
        ]);
    }

    /** @test */
    public function admin_can_update_system_settings()
    {
        $admin = $this->authenticateAs('platform_admin');

        $settings = [
            'maintenance_mode' => false,
            'registration_enabled' => true,
            'investment_enabled' => true,
            'minimum_investment_amount' => 5000,
            'platform_fee_percentage' => 2.5
        ];

        $response = $this->putJson('/api/admin/settings', $settings);

        $this->assertApiResponse($response);
        
        // Verify settings were updated
        $response = $this->getJson('/api/admin/settings');
        $data = $response->json('data.platform_settings');
        $this->assertEquals(5000, $data['minimum_investment_amount']);
        $this->assertEquals(2.5, $data['platform_fee_percentage']);
    }

    /** @test */
    public function admin_can_manage_document_templates()
    {
        $admin = $this->authenticateAs('platform_admin');

        $response = $this->getJson('/api/admin/settings/templates');

        $this->assertApiResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'templates' => [
                    '*' => [
                        'id',
                        'name',
                        'type',
                        'content',
                        'required_fields',
                        'is_active'
                    ]
                ]
            ]
        ]);
    }

    /** @test */
    public function admin_can_update_document_template()
    {
        $admin = $this->authenticateAs('platform_admin');
        $template = DocumentTemplate::factory()->create([
            'name' => 'Investment Agreement',
            'type' => 'investment_contract'
        ]);

        $updateData = [
            'name' => 'Updated Investment Agreement',
            'content' => 'Updated template content...',
            'required_fields' => ['investor_name', 'amount', 'project_name']
        ];

        $response = $this->putJson("/api/admin/settings/templates/{$template->id}", $updateData);

        $this->assertApiResponse($response);
        $this->assertDatabaseHas('document_templates', [
            'id' => $template->id,
            'name' => 'Updated Investment Agreement'
        ]);
    }

    /** @test */
    public function admin_status_update_validates_valid_status()
    {
        $admin = $this->authenticateAs('platform_admin');
        $user = User::factory()->create(['status' => 'pending']);

        $response = $this->putJson("/api/admin/users/{$user->id}/status", [
            'status' => 'invalid_status'
        ]);

        $this->assertValidationError($response, 'status');
    }

    /** @test */
    public function admin_status_update_requires_reason_for_suspension()
    {
        $admin = $this->authenticateAs('platform_admin');
        $user = User::factory()->create(['status' => 'active']);

        $response = $this->putJson("/api/admin/users/{$user->id}/status", [
            'status' => 'suspended'
            // Missing reason
        ]);

        $this->assertValidationError($response, 'reason');
    }
}
