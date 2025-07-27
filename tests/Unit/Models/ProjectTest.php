<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\Investment;
use App\Models\ProjectMedia;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function project_has_fillable_attributes()
    {
        $fillable = [
            'name', 'description', 'location', 'estimated_budget', 'actual_budget',
            'estimated_duration', 'actual_duration', 'start_date', 'end_date',
            'completion_date', 'project_type', 'status', 'progress_percentage',
            'investment_goal', 'current_investment', 'min_investment', 'roi_percentage',
            'risk_level', 'created_by'
        ];

        $project = new Project();
        $this->assertEquals($fillable, $project->getFillable());
    }

    /** @test */
    public function project_casts_attributes_correctly()
    {
        $casts = [
            'id' => 'int',
            'estimated_budget' => 'decimal:2',
            'actual_budget' => 'decimal:2',
            'estimated_duration' => 'int',
            'actual_duration' => 'int',
            'progress_percentage' => 'decimal:2',
            'investment_goal' => 'decimal:2',
            'current_investment' => 'decimal:2',
            'min_investment' => 'decimal:2',
            'roi_percentage' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'completion_date' => 'datetime',
            'created_by' => 'int'
        ];

        $project = new Project();
        foreach ($casts as $attribute => $expectedCast) {
            $this->assertEquals($expectedCast, $project->getCasts()[$attribute]);
        }
    }

    /** @test */
    public function project_belongs_to_creator()
    {
        $user = User::factory()->create(['role' => 'constructor']);
        $project = Project::factory()->create(['created_by' => $user->id]);

        $this->assertInstanceOf(User::class, $project->creator);
        $this->assertEquals($user->id, $project->creator->id);
    }

    /** @test */
    public function project_has_many_tasks()
    {
        $project = Project::factory()->create();
        $tasks = Task::factory(3)->create(['project_id' => $project->id]);

        $this->assertCount(3, $project->tasks);
        $this->assertInstanceOf(Task::class, $project->tasks->first());
    }

    /** @test */
    public function project_has_many_investments()
    {
        $project = Project::factory()->create();
        $investments = Investment::factory(2)->create(['project_id' => $project->id]);

        $this->assertCount(2, $project->investments);
        $this->assertInstanceOf(Investment::class, $project->investments->first());
    }

    /** @test */
    public function project_has_many_media_files()
    {
        $project = Project::factory()->create();
        $media = ProjectMedia::factory(4)->create(['project_id' => $project->id]);

        $this->assertCount(4, $project->media);
        $this->assertInstanceOf(ProjectMedia::class, $project->media->first());
    }

    /** @test */
    public function project_scope_active_returns_active_projects()
    {
        Project::factory()->create(['status' => 'active']);
        Project::factory()->create(['status' => 'completed']);
        Project::factory()->create(['status' => 'cancelled']);

        $activeProjects = Project::active()->get();

        $this->assertCount(1, $activeProjects);
        $this->assertEquals('active', $activeProjects->first()->status);
    }

    /** @test */
    public function project_scope_seeking_investment_returns_correct_projects()
    {
        Project::factory()->create(['status' => 'seeking_investment']);
        Project::factory()->create(['status' => 'active']);
        Project::factory()->create(['status' => 'planning']);

        $seekingProjects = Project::seekingInvestment()->get();

        $this->assertCount(1, $seekingProjects);
        $this->assertEquals('seeking_investment', $seekingProjects->first()->status);
    }

    /** @test */
    public function project_scope_by_type_filters_correctly()
    {
        Project::factory()->create(['project_type' => 'fix_and_flip']);
        Project::factory()->create(['project_type' => 'new_construction']);
        Project::factory()->create(['project_type' => 'renovation']);

        $fixFlipProjects = Project::byType('fix_and_flip')->get();

        $this->assertCount(1, $fixFlipProjects);
        $this->assertEquals('fix_and_flip', $fixFlipProjects->first()->project_type);
    }

    /** @test */
    public function project_scope_by_creator_filters_correctly()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Project::factory(2)->create(['created_by' => $user1->id]);
        Project::factory()->create(['created_by' => $user2->id]);

        $user1Projects = Project::byCreator($user1->id)->get();

        $this->assertCount(2, $user1Projects);
        $this->assertEquals($user1->id, $user1Projects->first()->created_by);
    }

    /** @test */
    public function project_can_calculate_progress_percentage()
    {
        $project = Project::factory()->create();
        
        // Create tasks with different statuses
        Task::factory(2)->create(['project_id' => $project->id, 'status' => 'completed']);
        Task::factory(3)->create(['project_id' => $project->id, 'status' => 'in_progress']);
        Task::factory(5)->create(['project_id' => $project->id, 'status' => 'pending']);

        $progress = $project->calculateProgress();

        // 2 completed out of 10 total = 20%
        $this->assertEquals(20.00, $progress);
    }

    /** @test */
    public function project_can_check_if_seeking_investment()
    {
        $seekingProject = Project::factory()->create(['status' => 'seeking_investment']);
        $activeProject = Project::factory()->create(['status' => 'active']);

        $this->assertTrue($seekingProject->isSeekingInvestment());
        $this->assertFalse($activeProject->isSeekingInvestment());
    }

    /** @test */
    public function project_can_check_if_fully_funded()
    {
        $fullyFunded = Project::factory()->create([
            'investment_goal' => 100000,
            'current_investment' => 100000
        ]);

        $partiallyFunded = Project::factory()->create([
            'investment_goal' => 100000,
            'current_investment' => 50000
        ]);

        $this->assertTrue($fullyFunded->isFullyFunded());
        $this->assertFalse($partiallyFunded->isFullyFunded());
    }

    /** @test */
    public function project_can_calculate_funding_percentage()
    {
        $project = Project::factory()->create([
            'investment_goal' => 100000,
            'current_investment' => 75000
        ]);

        $percentage = $project->getFundingPercentage();

        $this->assertEquals(75.00, $percentage);
    }

    /** @test */
    public function project_can_get_remaining_investment_needed()
    {
        $project = Project::factory()->create([
            'investment_goal' => 100000,
            'current_investment' => 30000
        ]);

        $remaining = $project->getRemainingInvestment();

        $this->assertEquals(70000, $remaining);
    }

    /** @test */
    public function project_can_add_investment()
    {
        $project = Project::factory()->create([
            'current_investment' => 50000
        ]);

        $project->addInvestment(25000);

        $this->assertEquals(75000, $project->current_investment);
    }

    /** @test */
    public function project_can_update_progress()
    {
        $project = Project::factory()->create(['progress_percentage' => 0]);

        $project->updateProgress(45.5);

        $this->assertEquals(45.5, $project->progress_percentage);
    }

    /** @test */
    public function project_can_mark_as_completed()
    {
        $project = Project::factory()->create([
            'status' => 'active',
            'completion_date' => null
        ]);

        $project->markAsCompleted();

        $this->assertEquals('completed', $project->status);
        $this->assertEquals(100, $project->progress_percentage);
        $this->assertNotNull($project->completion_date);
    }

    /** @test */
    public function project_can_get_estimated_roi()
    {
        $project = Project::factory()->create([
            'investment_goal' => 100000,
            'roi_percentage' => 15.5
        ]);

        $roi = $project->getEstimatedROI(20000);

        $this->assertEquals(3100, $roi); // 20000 * 15.5%
    }

    /** @test */
    public function project_validates_status_transitions()
    {
        $project = Project::factory()->create(['status' => 'planning']);

        $this->assertTrue($project->canTransitionTo('seeking_investment'));
        $this->assertFalse($project->canTransitionTo('completed'));
    }

    /** @test */
    public function project_can_get_duration_in_months()
    {
        $project = Project::factory()->create(['estimated_duration' => 365]);

        $months = $project->getDurationInMonths();

        $this->assertEquals(12, $months);
    }
}
