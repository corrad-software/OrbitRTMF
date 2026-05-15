<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\RtmfModule;
use App\Models\RtmfProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RtmfProjectRoleTest extends TestCase
{
    use RefreshDatabase;

    private Role $testerRole;
    private RtmfProject $project;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create([
            'name'        => 'admin',
            'description' => 'Full access',
            'permissions' => ['rtmf.view', 'rtmf.manage'],
        ]);

        $this->testerRole = Role::create([
            'name'        => 'tester',
            'description' => 'Read-only',
            'permissions' => ['rtmf.view'],
        ]);

        $this->project = RtmfProject::create([
            'code' => 'ROLETEST',
            'name' => 'Role Test Project',
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeUser(string $suffix, string $sysRole = 'tester'): User
    {
        $roleId = Role::where('name', $sysRole === 'admin' ? 'admin' : 'tester')->value('id');

        return User::create([
            'name'      => "User {$suffix}",
            'email'     => "user{$suffix}@test.local",
            'password'  => bcrypt('x'),
            'role'      => $sysRole,
            'role_id'   => $roleId,
            'is_active' => true,
        ]);
    }

    private function addProjectMember(User $user, string $projectRole): void
    {
        DB::table('rtmf_project_users')->insert([
            'project_id' => $this->project->id,
            'user_id'    => $user->id,
            'role'       => $projectRole,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // ── rtmfProjectRole ───────────────────────────────────────────────────────

    public function test_system_admin_always_gets_admin_project_role(): void
    {
        $admin = $this->makeUser('admin', 'admin');

        $this->assertEquals('admin', $admin->rtmfProjectRole($this->project->id));
    }

    public function test_project_member_returns_their_assigned_role(): void
    {
        $user = $this->makeUser('ba');
        $this->addProjectMember($user, 'business_analyst');

        $this->assertEquals('business_analyst', $user->rtmfProjectRole($this->project->id));
    }

    public function test_non_member_returns_null_project_role(): void
    {
        $user = $this->makeUser('stranger');

        $this->assertNull($user->rtmfProjectRole($this->project->id));
    }

    // ── canEditRtmfProject ───────────────────────────────────────────────────

    public function test_system_admin_can_edit(): void
    {
        $admin = $this->makeUser('admin2', 'admin');

        $this->assertTrue($admin->canEditRtmfProject($this->project->id));
    }

    public function test_business_analyst_can_edit(): void
    {
        $user = $this->makeUser('ba2');
        $this->addProjectMember($user, 'business_analyst');

        $this->assertTrue($user->canEditRtmfProject($this->project->id));
    }

    public function test_qa_cannot_edit(): void
    {
        $user = $this->makeUser('qa');
        $this->addProjectMember($user, 'qa');

        $this->assertFalse($user->canEditRtmfProject($this->project->id));
    }

    public function test_technical_cannot_edit(): void
    {
        $user = $this->makeUser('tech');
        $this->addProjectMember($user, 'technical');

        $this->assertFalse($user->canEditRtmfProject($this->project->id));
    }

    public function test_viewer_cannot_edit(): void
    {
        $user = $this->makeUser('viewer');
        $this->addProjectMember($user, 'viewer');

        $this->assertFalse($user->canEditRtmfProject($this->project->id));
    }

    public function test_non_member_cannot_edit(): void
    {
        $user = $this->makeUser('nobody');

        $this->assertFalse($user->canEditRtmfProject($this->project->id));
    }

    // ── denyIfCannotEdit via HTTP ─────────────────────────────────────────────

    public function test_qa_member_gets_403_on_module_create(): void
    {
        $qa = $this->makeUser('qa2');
        $this->addProjectMember($qa, 'qa');

        $this->actingAs($qa)
            ->postJson('/api/rtmf-modules', [
                'code'       => 'QAMOD',
                'name'       => 'QA Module',
                'project_id' => $this->project->id,
            ])
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'FORBIDDEN');
    }

    public function test_ba_member_can_create_module(): void
    {
        $ba = $this->makeUser('ba3');
        $this->addProjectMember($ba, 'business_analyst');

        $res = $this->actingAs($ba)->postJson('/api/rtmf-modules', [
            'code'       => 'BAMOD',
            'name'       => 'BA Module',
            'project_id' => $this->project->id,
        ]);

        $res->assertOk();
        $this->assertDatabaseHas('rtmf_modules', ['code' => 'BAMOD', 'project_id' => $this->project->id]);
    }

    public function test_viewer_gets_403_on_module_create(): void
    {
        $viewer = $this->makeUser('viewer2');
        $this->addProjectMember($viewer, 'viewer');

        $this->actingAs($viewer)
            ->postJson('/api/rtmf-modules', [
                'code'       => 'VMOD',
                'name'       => 'Viewer Module',
                'project_id' => $this->project->id,
            ])
            ->assertStatus(403);
    }

    public function test_project_admin_member_can_create_module(): void
    {
        $projectAdmin = $this->makeUser('padmin');
        $this->addProjectMember($projectAdmin, 'admin');

        $res = $this->actingAs($projectAdmin)->postJson('/api/rtmf-modules', [
            'code'       => 'PAMOD',
            'name'       => 'Project Admin Module',
            'project_id' => $this->project->id,
        ]);

        $res->assertOk();
    }
}
