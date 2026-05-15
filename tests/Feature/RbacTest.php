<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\RtmfFrontend;
use App\Models\RtmfModule;
use App\Models\RtmfProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $tester;
    private User $noRole;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create([
            'name' => 'admin',
            'description' => 'Full access',
            'permissions' => ['rtmf.view', 'rtmf.manage', 'posts.view', 'users.view'],
        ]);

        $testerRole = Role::create([
            'name' => 'tester',
            'description' => 'Read-only RTMF',
            'permissions' => ['rtmf.view'],
        ]);

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.local',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'role_id' => $adminRole->id,
            'is_active' => true,
        ]);

        $this->tester = User::create([
            'name' => 'Tester User',
            'email' => 'tester@test.local',
            'password' => bcrypt('password'),
            'role' => 'tester',
            'role_id' => $testerRole->id,
            'is_active' => true,
        ]);

        $this->noRole = User::create([
            'name' => 'No Role User',
            'email' => 'norole@test.local',
            'password' => bcrypt('password'),
            'role' => 'editor',
            'role_id' => null,
            'is_active' => true,
        ]);
    }

    // ── Unauthenticated ────────────────────────────────────────────────────────

    public function test_unauthenticated_cannot_access_rtmf_list(): void
    {
        $response = $this->getJson('/api/rtmf-frontends');
        $response->assertStatus(401);
    }

    public function test_unauthenticated_cannot_access_rtmf_dashboard(): void
    {
        $response = $this->getJson('/api/rtmf/dashboard');
        $response->assertStatus(401);
    }

    // ── Admin role ─────────────────────────────────────────────────────────────

    public function test_admin_can_list_rtmf_frontends(): void
    {
        $this->actingAs($this->admin);
        $response = $this->getJson('/api/rtmf-frontends');
        $response->assertStatus(200)->assertJsonStructure(['data', 'meta']);
    }

    public function test_admin_can_access_rtmf_dashboard(): void
    {
        $this->actingAs($this->admin);
        $response = $this->getJson('/api/rtmf/dashboard');
        $response->assertStatus(200);
    }

    public function test_admin_can_list_rtmf_modules(): void
    {
        $this->actingAs($this->admin);
        $response = $this->getJson('/api/rtmf-modules');
        $response->assertStatus(200)->assertJsonStructure(['data']);
    }

    public function test_admin_can_list_rtmf_actors(): void
    {
        $this->actingAs($this->admin);
        $response = $this->getJson('/api/rtmf-actors');
        $response->assertStatus(200)->assertJsonStructure(['data']);
    }

    public function test_admin_can_create_rtmf_module(): void
    {
        $this->actingAs($this->admin);
        $response = $this->postJson('/api/rtmf-modules', [
            'code' => 'TST',
            'name' => 'Test Module',
            'sortOrder' => 1,
        ]);
        $response->assertStatus(200)->assertJsonPath('data.code', 'TST');
    }

    public function test_admin_can_update_rtmf_module(): void
    {
        $this->actingAs($this->admin);

        $create = $this->postJson('/api/rtmf-modules', [
            'code' => 'TST2',
            'name' => 'Test Module 2',
            'sortOrder' => 2,
        ]);
        $create->assertStatus(200);
        $id = $create->json('data.id');

        $response = $this->putJson("/api/rtmf-modules/{$id}", [
            'code' => 'TST2',
            'name' => 'Updated Module',
            'sortOrder' => 2,
        ]);
        $response->assertStatus(200)->assertJsonPath('data.name', 'Updated Module');
    }

    public function test_admin_can_delete_rtmf_module(): void
    {
        $this->actingAs($this->admin);

        $create = $this->postJson('/api/rtmf-modules', [
            'code' => 'DEL',
            'name' => 'Delete Me',
            'sortOrder' => 99,
        ]);
        $create->assertStatus(200);
        $id = $create->json('data.id');

        $response = $this->deleteJson("/api/rtmf-modules/{$id}");
        $response->assertStatus(200)->assertJsonPath('data.success', true);
    }

    public function test_admin_role_bypass_works_with_uppercase_role(): void
    {
        // Simulate old data with capital-A 'Admin' — hasPermission() should still pass
        $this->admin->role = 'Admin';
        $this->admin->save();

        $this->actingAs($this->admin);
        $response = $this->getJson('/api/rtmf-frontends');
        $response->assertStatus(200);
    }

    // ── Tester role ────────────────────────────────────────────────────────────

    public function test_tester_can_list_rtmf_frontends(): void
    {
        $this->actingAs($this->tester);
        $response = $this->getJson('/api/rtmf-frontends');
        $response->assertStatus(200)->assertJsonStructure(['data', 'meta']);
    }

    public function test_tester_can_access_rtmf_dashboard(): void
    {
        $this->actingAs($this->tester);
        $response = $this->getJson('/api/rtmf/dashboard');
        $response->assertStatus(200);
    }

    public function test_tester_can_list_rtmf_modules(): void
    {
        $this->actingAs($this->tester);
        $response = $this->getJson('/api/rtmf-modules');
        $response->assertStatus(200);
    }

    public function test_tester_can_list_rtmf_actors(): void
    {
        $this->actingAs($this->tester);
        $response = $this->getJson('/api/rtmf-actors');
        $response->assertStatus(200);
    }

    public function test_tester_cannot_create_rtmf_frontend(): void
    {
        $project = RtmfProject::create(['code' => 'RBAC', 'name' => 'RBAC Project']);
        $module  = RtmfModule::create(['code' => 'MOD', 'name' => 'Module', 'project_id' => $project->id]);

        $this->actingAs($this->tester);
        $response = $this->postJson('/api/rtmf-frontends', [
            'title'    => 'Should Fail',
            'specId'   => 'TST-001',
            'moduleId' => $module->id,
        ]);
        $response->assertStatus(403)->assertJsonPath('error.code', 'FORBIDDEN');
    }

    public function test_tester_cannot_update_rtmf_frontend(): void
    {
        $project  = RtmfProject::create(['code' => 'RBAC2', 'name' => 'RBAC Project 2']);
        $module   = RtmfModule::create(['code' => 'MOD2', 'name' => 'Module 2', 'project_id' => $project->id]);
        $frontend = RtmfFrontend::create(['spec_id' => 'F-001', 'module_id' => $module->id, 'title' => 'Existing']);

        $this->actingAs($this->tester);
        $response = $this->putJson("/api/rtmf-frontends/{$frontend->id}", ['title' => 'Hacked']);
        $response->assertStatus(403)->assertJsonPath('error.code', 'FORBIDDEN');
    }

    public function test_tester_cannot_delete_rtmf_frontend(): void
    {
        $project  = RtmfProject::create(['code' => 'RBAC3', 'name' => 'RBAC Project 3']);
        $module   = RtmfModule::create(['code' => 'MOD3', 'name' => 'Module 3', 'project_id' => $project->id]);
        $frontend = RtmfFrontend::create(['spec_id' => 'F-002', 'module_id' => $module->id, 'title' => 'To Delete']);

        $this->actingAs($this->tester);
        $response = $this->deleteJson("/api/rtmf-frontends/{$frontend->id}");
        $response->assertStatus(403)->assertJsonPath('error.code', 'FORBIDDEN');
    }

    public function test_tester_cannot_create_rtmf_module(): void
    {
        $this->actingAs($this->tester);
        $response = $this->postJson('/api/rtmf-modules', ['code' => 'BAD', 'name' => 'Bad', 'sortOrder' => 1]);
        $response->assertStatus(403)->assertJsonPath('error.code', 'FORBIDDEN');
    }

    public function test_tester_cannot_create_rtmf_actor(): void
    {
        $this->actingAs($this->tester);
        $response = $this->postJson('/api/rtmf-actors', ['name' => 'Evil Actor']);
        $response->assertStatus(403)->assertJsonPath('error.code', 'FORBIDDEN');
    }

    // ── No-role user ───────────────────────────────────────────────────────────

    public function test_user_with_no_role_cannot_access_rtmf_view(): void
    {
        $this->actingAs($this->noRole);
        $response = $this->getJson('/api/rtmf-frontends');
        $response->assertStatus(403)->assertJsonPath('error.code', 'FORBIDDEN');
    }

    public function test_user_with_no_role_cannot_access_rtmf_dashboard(): void
    {
        $this->actingAs($this->noRole);
        $response = $this->getJson('/api/rtmf/dashboard');
        $response->assertStatus(403)->assertJsonPath('error.code', 'FORBIDDEN');
    }

    public function test_user_with_no_role_cannot_write_rtmf(): void
    {
        $this->actingAs($this->noRole);
        $response = $this->postJson('/api/rtmf-frontends', ['title' => 'Nope']);
        $response->assertStatus(403)->assertJsonPath('error.code', 'FORBIDDEN');
    }

    // ── hasPermission unit-level checks ───────────────────────────────────────

    public function test_admin_has_permission_returns_true_for_any_permission(): void
    {
        $this->assertTrue($this->admin->hasPermission('rtmf.view'));
        $this->assertTrue($this->admin->hasPermission('rtmf.manage'));
        $this->assertTrue($this->admin->hasPermission('users.delete'));
        $this->assertTrue($this->admin->hasPermission('anything.at.all'));
    }

    public function test_tester_has_permission_returns_true_only_for_rtmf_view(): void
    {
        $this->assertTrue($this->tester->hasPermission('rtmf.view'));
        $this->assertFalse($this->tester->hasPermission('rtmf.manage'));
        $this->assertFalse($this->tester->hasPermission('users.view'));
        $this->assertFalse($this->tester->hasPermission('posts.view'));
    }

    public function test_user_with_no_role_has_no_permissions(): void
    {
        $this->assertFalse($this->noRole->hasPermission('rtmf.view'));
        $this->assertFalse($this->noRole->hasPermission('rtmf.manage'));
        $this->assertFalse($this->noRole->hasPermission('posts.view'));
    }
}
