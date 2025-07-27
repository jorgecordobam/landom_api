<?php

namespace Tests\Feature\Integration;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Investment;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectLifecycleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function complete_project_lifecycle_works_correctly()
    {
        // 1. Constructor creates a project
        $constructor = $this->authenticateAs('constructor', [
            'company_name' => 'Test Construction Co.'
        ]);

        $projectData = [
            'name' => 'Luxury Home Renovation',
            'description' => 'Complete renovation of a 3-bedroom house',
            'location' => '123 Main St, Anytown',
            'estimated_budget' => 150000,
            'estimated_duration' => 120,
            'project_type' => 'fix_and_flip',
            'investment_goal' => 100000,
            'min_investment' => 5000,
            'roi_percentage' => 18.5
        ];

        $response = $this->postJson('/api/projects', $projectData);
        $this->assertApiResponse($response, 201);
        $projectId = $response->json('data.id');

        // 2. Constructor adds tasks to the project
        $taskData = [
            'name' => 'Demolition Phase',
            'description' => 'Remove old fixtures and prepare for renovation',
            'phase' => 'demolition',
            'estimated_hours' => 40,
            'estimated_cost' => 5000,
            'due_date' => now()->addDays(14)->format('Y-m-d')
        ];

        $response = $this->postJson("/api/projects/{$projectId}/tasks", $taskData);
        $this->assertApiResponse($response, 201);

        // 3. Project status is updated to seeking investment
        $response = $this->putJson("/api/projects/{$projectId}", [
            'status' => 'seeking_investment'
        ]);
        $this->assertApiResponse($response);

        // 4. Investor views investment opportunities
        $investor = $this->authenticateAs('investor', [
            'status' => 'verified'
        ]);

        $response = $this->getJson('/api/investor/investments/opportunities');
        $this->assertApiResponse($response);
        $this->assertGreaterThan(0, count($response->json('data.opportunities')));

        // 5. Investor calculates potential returns
        $response = $this->postJson('/api/investor/investments/calculate-returns', [
            'project_id' => $projectId,
            'amount' => 25000
        ]);
        $this->assertApiResponse($response);
        $this->assertEquals(25000, $response->json('data.investment_amount'));

        // 6. Investor makes an investment
        $response = $this->postJson('/api/investor/investments', [
            'project_id' => $projectId,
            'amount' => 25000,
            'investment_type' => 'equity'
        ]);
        $this->assertApiResponse($response, 201);

        // 7. Switch back to constructor and start the project
        $this->authenticateAs('constructor', ['id' => $constructor->id]);

        $response = $this->putJson("/api/projects/{$projectId}", [
            'status' => 'active',
            'start_date' => now()->format('Y-m-d')
        ]);
        $this->assertApiResponse($response);

        // 8. Update task progress
        $tasks = Task::where('project_id', $projectId)->get();
        $task = $tasks->first();

        $response = $this->putJson("/api/projects/{$projectId}/tasks/{$task->id}/status", [
            'status' => 'completed',
            'actual_hours' => 38,
            'actual_cost' => 4800
        ]);
        $this->assertApiResponse($response);

        // 9. Check project progress
        $response = $this->getJson("/api/projects/{$projectId}/progress");
        $this->assertApiResponse($response);
        $this->assertGreaterThan(0, $response->json('data.progress_percentage'));

        // 10. Investor checks their investment status
        $this->authenticateAs('investor', ['id' => $investor->id]);

        $response = $this->getJson('/api/profile/investments');
        $this->assertApiResponse($response);
        $this->assertCount(1, $response->json('data.investments'));

        // Verify the entire lifecycle worked
        $project = Project::find($projectId);
        $this->assertEquals('active', $project->status);
        $this->assertEquals(25000, $project->current_investment);

        $investment = Investment::where('project_id', $projectId)->first();
        $this->assertEquals($investor->id, $investment->investor_id);
        $this->assertEquals('pending', $investment->status); // Should be pending approval
    }

    /** @test */
    public function investment_workflow_with_multiple_investors()
    {
        $constructor = $this->authenticateAs('constructor');
        
        $project = Project::factory()->create([
            'created_by' => $constructor->id,
            'status' => 'seeking_investment',
            'investment_goal' => 100000,
            'min_investment' => 10000,
            'current_investment' => 0
        ]);

        // Create multiple investors
        $investor1 = User::factory()->create(['role' => 'investor', 'status' => 'verified']);
        $investor2 = User::factory()->create(['role' => 'investor', 'status' => 'verified']);
        $investor3 = User::factory()->create(['role' => 'investor', 'status' => 'verified']);

        // First investor invests
        $this->authenticateAs('investor', ['id' => $investor1->id]);
        $response = $this->postJson('/api/investor/investments', [
            'project_id' => $project->id,
            'amount' => 30000,
            'investment_type' => 'equity'
        ]);
        $this->assertApiResponse($response, 201);

        // Second investor invests
        $this->authenticateAs('investor', ['id' => $investor2->id]);
        $response = $this->postJson('/api/investor/investments', [
            'project_id' => $project->id,
            'amount' => 40000,
            'investment_type' => 'equity'
        ]);
        $this->assertApiResponse($response, 201);

        // Third investor tries to invest more than remaining
        $this->authenticateAs('investor', ['id' => $investor3->id]);
        $response = $this->postJson('/api/investor/investments', [
            'project_id' => $project->id,
            'amount' => 50000, // Would exceed investment goal
            'investment_type' => 'equity'
        ]);
        
        // Should either limit to remaining amount or reject
        $project->refresh();
        $this->assertLessThanOrEqual(100000, $project->current_investment);

        // Verify total investments don't exceed goal
        $totalInvestments = Investment::where('project_id', $project->id)->sum('amount');
        $this->assertLessThanOrEqual(100000, $totalInvestments);
    }

    /** @test */
    public function project_completion_distributes_returns()
    {
        $constructor = $this->authenticateAs('constructor');
        $investor = User::factory()->create(['role' => 'investor', 'status' => 'verified']);

        $project = Project::factory()->create([
            'created_by' => $constructor->id,
            'status' => 'active',
            'investment_goal' => 50000,
            'current_investment' => 50000,
            'roi_percentage' => 20.0
        ]);

        $investment = Investment::factory()->create([
            'project_id' => $project->id,
            'investor_id' => $investor->id,
            'amount' => 50000,
            'status' => 'active',
            'expected_return' => 10000 // 20% of 50000
        ]);

        // Complete the project
        $response = $this->putJson("/api/projects/{$project->id}", [
            'status' => 'completed',
            'completion_date' => now()->format('Y-m-d H:i:s'),
            'actual_budget' => 145000 // Came in under budget
        ]);
        $this->assertApiResponse($response);

        // Check that returns are calculated
        $response = $this->getJson("/api/projects/{$project->id}");
        $this->assertApiResponse($response);
        $this->assertEquals('completed', $response->json('data.status'));

        // Investor should see their returns
        $this->authenticateAs('investor', ['id' => $investor->id]);
        $response = $this->getJson('/api/profile/investments');
        $this->assertApiResponse($response);

        $investmentData = $response->json('data.investments.0');
        $this->assertGreaterThan(0, $investmentData['current_return']);
    }

    /** @test */
    public function admin_can_intervene_in_problematic_project()
    {
        $constructor = User::factory()->create(['role' => 'constructor']);
        $admin = $this->authenticateAs('platform_admin');

        $project = Project::factory()->create([
            'created_by' => $constructor->id,
            'status' => 'active',
            'estimated_duration' => 90,
            'start_date' => now()->subDays(150) // Overdue
        ]);

        // Admin reviews overdue projects
        $response = $this->getJson('/api/admin/projects');
        $this->assertApiResponse($response);

        // Admin investigates specific project
        $response = $this->getJson("/api/admin/projects/{$project->id}");
        $this->assertApiResponse($response);

        // Admin suspends the project due to delays
        $response = $this->putJson("/api/admin/projects/{$project->id}/status", [
            'status' => 'suspended',
            'reason' => 'Project is significantly overdue without proper communication'
        ]);
        $this->assertApiResponse($response);

        // Verify project status changed
        $project->refresh();
        $this->assertEquals('suspended', $project->status);

        // Constructor should see the suspension when they log in
        $this->authenticateAs('constructor', ['id' => $constructor->id]);
        $response = $this->getJson('/api/projects');
        
        $suspendedProjects = collect($response->json('data.projects'))
            ->where('status', 'suspended');
        $this->assertGreaterThan(0, $suspendedProjects->count());
    }

    /** @test */
    public function platform_handles_concurrent_investments()
    {
        $constructor = $this->authenticateAs('constructor');
        
        $project = Project::factory()->create([
            'created_by' => $constructor->id,
            'status' => 'seeking_investment',
            'investment_goal' => 50000,
            'min_investment' => 5000,
            'current_investment' => 40000 // Only 10k remaining
        ]);

        $investor1 = User::factory()->create(['role' => 'investor', 'status' => 'verified']);
        $investor2 = User::factory()->create(['role' => 'investor', 'status' => 'verified']);

        // Both investors try to invest 10k simultaneously
        $responses = [];

        // Simulate concurrent requests
        $this->authenticateAs('investor', ['id' => $investor1->id]);
        $responses[] = $this->postJson('/api/investor/investments', [
            'project_id' => $project->id,
            'amount' => 10000,
            'investment_type' => 'equity'
        ]);

        $this->authenticateAs('investor', ['id' => $investor2->id]);
        $responses[] = $this->postJson('/api/investor/investments', [
            'project_id' => $project->id,
            'amount' => 10000,
            'investment_type' => 'equity'
        ]);

        // One should succeed, one should fail or be adjusted
        $successCount = 0;
        foreach ($responses as $response) {
            if ($response->status() === 201) {
                $successCount++;
            }
        }

        // Verify that we don't exceed investment goal
        $project->refresh();
        $this->assertLessThanOrEqual(50000, $project->current_investment);
        
        // At least one investment should have been processed
        $this->assertGreaterThan(0, $successCount);
    }
}
