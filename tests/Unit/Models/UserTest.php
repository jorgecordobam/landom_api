<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Investment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_has_fillable_attributes()
    {
        $fillable = [
            'name', 'email', 'password', 'role', 'phone', 'company_name',
            'is_verified', 'email_verified_at', 'status', 'verification_token',
            'reset_password_token', 'avatar'
        ];

        $user = new User();
        $this->assertEquals($fillable, $user->getFillable());
    }

    /** @test */
    public function user_hides_sensitive_attributes()
    {
        $hidden = [
            'password', 'remember_token', 'verification_token', 'reset_password_token'
        ];

        $user = new User();
        $this->assertEquals($hidden, $user->getHidden());
    }

    /** @test */
    public function user_casts_attributes_correctly()
    {
        $casts = [
            'id' => 'int',
            'email_verified_at' => 'datetime',
            'is_verified' => 'boolean',
            'password' => 'hashed'
        ];

        $user = new User();
        foreach ($casts as $attribute => $expectedCast) {
            $this->assertEquals($expectedCast, $user->getCasts()[$attribute]);
        }
    }

    /** @test */
    public function user_password_is_automatically_hashed()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'plaintext',
            'role' => 'general_user'
        ]);

        $this->assertNotEquals('plaintext', $user->password);
        $this->assertTrue(Hash::check('plaintext', $user->password));
    }

    /** @test */
    public function user_has_projects_relationship()
    {
        $user = User::factory()->create(['role' => 'constructor']);
        $project = Project::factory()->create(['created_by' => $user->id]);

        $this->assertTrue($user->projects()->exists());
        $this->assertEquals($project->id, $user->projects->first()->id);
    }

    /** @test */
    public function user_has_investments_relationship()
    {
        $user = User::factory()->create(['role' => 'investor']);
        $investment = Investment::factory()->create(['investor_id' => $user->id]);

        $this->assertTrue($user->investments()->exists());
        $this->assertEquals($investment->id, $user->investments->first()->id);
    }

    /** @test */
    public function user_scope_verified_only_returns_verified_users()
    {
        User::factory()->create(['is_verified' => true]);
        User::factory()->create(['is_verified' => false]);

        $verifiedUsers = User::verified()->get();

        $this->assertCount(1, $verifiedUsers);
        $this->assertTrue($verifiedUsers->first()->is_verified);
    }

    /** @test */
    public function user_scope_active_only_returns_active_users()
    {
        User::factory()->create(['status' => 'active']);
        User::factory()->create(['status' => 'inactive']);
        User::factory()->create(['status' => 'pending']);

        $activeUsers = User::active()->get();

        $this->assertCount(1, $activeUsers);
        $this->assertEquals('active', $activeUsers->first()->status);
    }

    /** @test */
    public function user_scope_by_role_filters_correctly()
    {
        User::factory()->create(['role' => 'constructor']);
        User::factory()->create(['role' => 'investor']);
        User::factory()->create(['role' => 'general_user']);

        $constructors = User::byRole('constructor')->get();
        $investors = User::byRole('investor')->get();

        $this->assertCount(1, $constructors);
        $this->assertCount(1, $investors);
        $this->assertEquals('constructor', $constructors->first()->role);
        $this->assertEquals('investor', $investors->first()->role);
    }

    /** @test */
    public function user_can_check_if_constructor()
    {
        $constructor = User::factory()->create(['role' => 'constructor']);
        $investor = User::factory()->create(['role' => 'investor']);

        $this->assertTrue($constructor->isConstructor());
        $this->assertFalse($investor->isConstructor());
    }

    /** @test */
    public function user_can_check_if_investor()
    {
        $constructor = User::factory()->create(['role' => 'constructor']);
        $investor = User::factory()->create(['role' => 'investor']);

        $this->assertFalse($constructor->isInvestor());
        $this->assertTrue($investor->isInvestor());
    }

    /** @test */
    public function user_can_check_if_admin()
    {
        $admin = User::factory()->create(['role' => 'platform_admin']);
        $user = User::factory()->create(['role' => 'general_user']);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($user->isAdmin());
    }

    /** @test */
    public function user_can_get_full_name()
    {
        $user = User::factory()->create(['name' => 'John Doe']);

        $this->assertEquals('John Doe', $user->getFullName());
    }

    /** @test */
    public function user_can_get_avatar_url()
    {
        $userWithAvatar = User::factory()->create(['avatar' => 'avatars/user123.jpg']);
        $userWithoutAvatar = User::factory()->create(['avatar' => null]);

        $this->assertStringContains('avatars/user123.jpg', $userWithAvatar->getAvatarUrl());
        $this->assertStringContains('default-avatar', $userWithoutAvatar->getAvatarUrl());
    }

    /** @test */
    public function user_can_generate_verification_token()
    {
        $user = User::factory()->create(['verification_token' => null]);

        $token = $user->generateVerificationToken();

        $this->assertNotNull($token);
        $this->assertEquals($token, $user->verification_token);
        $this->assertEquals(32, strlen($token));
    }

    /** @test */
    public function user_can_verify_email()
    {
        $user = User::factory()->create([
            'is_verified' => false,
            'email_verified_at' => null,
            'verification_token' => 'test_token'
        ]);

        $user->markEmailAsVerified();

        $this->assertTrue($user->is_verified);
        $this->assertNotNull($user->email_verified_at);
        $this->assertNull($user->verification_token);
    }

    /** @test */
    public function user_can_update_status()
    {
        $user = User::factory()->create(['status' => 'pending']);

        $user->updateStatus('active');

        $this->assertEquals('active', $user->status);
    }

    /** @test */
    public function user_validates_role_enum()
    {
        $validRoles = ['general_user', 'constructor', 'investor', 'worker', 'project_admin', 'platform_admin'];

        foreach ($validRoles as $role) {
            $user = User::factory()->make(['role' => $role]);
            $this->assertNotNull($user);
        }

        // Invalid role should trigger validation error when saved
        $this->expectException(\Exception::class);
        User::create([
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => 'password',
            'role' => 'invalid_role'
        ]);
    }

    /** @test */
    public function user_validates_status_enum()
    {
        $validStatuses = ['pending', 'active', 'inactive', 'suspended', 'verified'];

        foreach ($validStatuses as $status) {
            $user = User::factory()->make(['status' => $status]);
            $this->assertNotNull($user);
        }
    }
}
