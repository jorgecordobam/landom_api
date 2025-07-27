<?php

namespace Tests\Feature\Investor;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Investment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvestmentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function investor_can_view_investment_opportunities()
    {
        $investor = $this->authenticateAs('investor');

        // Create some projects available for investment
        Project::factory(3)->create([
            'status' => 'seeking_investment',
            'investment_goal' => 100000,
            'min_investment' => 5000
        ]);

        $response = $this->getJson('/api/investor/investments/opportunities');

        $this->assertApiResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'opportunities' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'investment_goal',
                        'min_investment',
                        'current_investment',
                        'roi_percentage',
                        'estimated_duration',
                        'risk_level'
                    ]
                ]
            ]
        ]);
    }

    /** @test */
    public function investor_can_make_investment()
    {
        $investor = $this->authenticateAs('investor', [
            'status' => 'verified'
        ]);

        $project = Project::factory()->create([
            'status' => 'seeking_investment',
            'investment_goal' => 100000,
            'min_investment' => 5000,
            'current_investment' => 20000
        ]);

        $investmentData = [
            'project_id' => $project->id,
            'amount' => 10000,
            'investment_type' => 'equity'
        ];

        $response = $this->postJson('/api/investor/investments', $investmentData);

        $this->assertApiResponse($response, 201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'investment' => [
                    'id',
                    'project_id',
                    'investor_id',
                    'amount',
                    'investment_type',
                    'status',
                    'expected_return'
                ]
            ]
        ]);

        $this->assertDatabaseHas('investments', [
            'project_id' => $project->id,
            'investor_id' => $investor->id,
            'amount' => 10000,
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function investment_requires_minimum_amount()
    {
        $investor = $this->authenticateAs('investor', ['status' => 'verified']);

        $project = Project::factory()->create([
            'status' => 'seeking_investment',
            'min_investment' => 5000
        ]);

        $investmentData = [
            'project_id' => $project->id,
            'amount' => 3000,
            'investment_type' => 'equity'
        ];

        $response = $this->postJson('/api/investor/investments', $investmentData);

        $this->assertValidationError($response, 'amount');
    }

    /** @test */
    public function unverified_investor_cannot_make_investment()
    {
        $investor = $this->authenticateAs('investor', [
            'status' => 'pending'
        ]);

        $project = Project::factory()->create([
            'status' => 'seeking_investment'
        ]);

        $investmentData = [
            'project_id' => $project->id,
            'amount' => 10000,
            'investment_type' => 'equity'
        ];

        $response = $this->postJson('/api/investor/investments', $investmentData);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'Investor must be verified to make investments'
        ]);
    }

    /** @test */
    public function non_investor_cannot_make_investment()
    {
        $this->authenticateAs('general_user');

        $project = Project::factory()->create([
            'status' => 'seeking_investment'
        ]);

        $investmentData = [
            'project_id' => $project->id,
            'amount' => 10000,
            'investment_type' => 'equity'
        ];

        $response = $this->postJson('/api/investor/investments', $investmentData);

        $response->assertStatus(403);
    }

    /** @test */
    public function cannot_invest_in_non_seeking_project()
    {
        $investor = $this->authenticateAs('investor', ['status' => 'verified']);

        $project = Project::factory()->create([
            'status' => 'active' // Not seeking investment
        ]);

        $investmentData = [
            'project_id' => $project->id,
            'amount' => 10000,
            'investment_type' => 'equity'
        ];

        $response = $this->postJson('/api/investor/investments', $investmentData);

        $this->assertValidationError($response, 'project_id');
    }

    /** @test */
    public function investor_can_calculate_potential_returns()
    {
        $investor = $this->authenticateAs('investor');

        $project = Project::factory()->create([
            'status' => 'seeking_investment',
            'investment_goal' => 100000,
            'estimated_duration' => 12,
            'roi_percentage' => 15.5
        ]);

        $calculationData = [
            'project_id' => $project->id,
            'amount' => 20000
        ];

        $response = $this->postJson('/api/investor/investments/calculate-returns', $calculationData);

        $this->assertApiResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'investment_amount',
                'estimated_return',
                'total_return',
                'roi_percentage',
                'estimated_duration',
                'monthly_return',
                'risk_assessment'
            ]
        ]);

        $data = $response->json('data');
        $this->assertEquals(20000, $data['investment_amount']);
        $this->assertEquals(15.5, $data['roi_percentage']);
    }

    /** @test */
    public function investor_can_view_their_investments()
    {
        $investor = $this->authenticateAs('investor');
        $otherInvestor = User::factory()->create(['role' => 'investor']);

        // Create investments for the authenticated investor
        Investment::factory(3)->create([
            'investor_id' => $investor->id,
            'status' => 'active'
        ]);

        // Create investment for another investor (should not be visible)
        Investment::factory()->create([
            'investor_id' => $otherInvestor->id,
            'status' => 'active'
        ]);

        $response = $this->getJson('/api/profile/investments');

        $this->assertApiResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'investments' => [
                    '*' => [
                        'id',
                        'project',
                        'amount',
                        'investment_type',
                        'status',
                        'expected_return',
                        'current_return',
                        'investment_date'
                    ]
                ],
                'summary' => [
                    'total_invested',
                    'total_returns',
                    'active_investments',
                    'average_roi'
                ]
            ]
        ]);

        $this->assertCount(3, $response->json('data.investments'));
    }

    /** @test */
    public function investor_can_view_investment_statistics()
    {
        $investor = $this->authenticateAs('investor');

        Investment::factory(2)->create([
            'investor_id' => $investor->id,
            'amount' => 10000,
            'status' => 'active'
        ]);

        Investment::factory()->create([
            'investor_id' => $investor->id,
            'amount' => 5000,
            'status' => 'completed'
        ]);

        $response = $this->getJson('/api/profile/investments/statistics');

        $this->assertApiResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'total_invested',
                'total_returns',
                'active_investments',
                'completed_investments',
                'average_roi',
                'portfolio_diversity',
                'performance_trend' => [
                    'monthly',
                    'quarterly',
                    'yearly'
                ]
            ]
        ]);

        $data = $response->json('data');
        $this->assertEquals(25000, $data['total_invested']);
        $this->assertEquals(2, $data['active_investments']);
        $this->assertEquals(1, $data['completed_investments']);
    }

    /** @test */
    public function investor_can_view_investment_history()
    {
        $investor = $this->authenticateAs('investor');

        Investment::factory(5)->create([
            'investor_id' => $investor->id
        ]);

        $response = $this->getJson('/api/profile/investments/history');

        $this->assertApiResponse($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'history' => [
                    '*' => [
                        'id',
                        'project',
                        'amount',
                        'investment_date',
                        'status',
                        'returns',
                        'duration'
                    ]
                ],
                'pagination'
            ]
        ]);
    }

    /** @test */
    public function investor_can_update_investment()
    {
        $investor = $this->authenticateAs('investor');

        $investment = Investment::factory()->create([
            'investor_id' => $investor->id,
            'status' => 'pending'
        ]);

        $updateData = [
            'notes' => 'Updated investment notes'
        ];

        $response = $this->putJson("/api/investor/investments/{$investment->id}", $updateData);

        $this->assertApiResponse($response);
        $this->assertDatabaseHas('investments', [
            'id' => $investment->id,
            'notes' => 'Updated investment notes'
        ]);
    }

    /** @test */
    public function investor_cannot_update_others_investment()
    {
        $investor = $this->authenticateAs('investor');
        $otherInvestor = User::factory()->create(['role' => 'investor']);

        $investment = Investment::factory()->create([
            'investor_id' => $otherInvestor->id
        ]);

        $updateData = [
            'notes' => 'Hacked notes'
        ];

        $response = $this->putJson("/api/investor/investments/{$investment->id}", $updateData);

        $response->assertStatus(403);
    }
}
