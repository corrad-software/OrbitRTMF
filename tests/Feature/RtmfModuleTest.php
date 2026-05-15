<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\RtmfModule;
use App\Models\RtmfSubModule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RtmfModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $viewer;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create([
            'name'        => 'admin',
            'description' => 'Full access',
            'permissions' => ['rtmf.view', 'rtmf.manage'],
        ]);

        $viewerRole = Role::create([
            'name'        => 'viewer',
            'description' => 'Read-only',
            'permissions' => ['rtmf.view'],
        ]);

        $this->admin = User::create([
            'name'      => 'Admin',
            'email'     => 'admin@test.local',
            'password'  => bcrypt('x'),
            'role'      => 'admin',
            'role_id'   => $adminRole->id,
            'is_active' => true,
        ]);

        $this->viewer = User::create([
            'name'      => 'Viewer',
            'email'     => 'viewer@test.local',
            'password'  => bcrypt('x'),
            'role'      => 'viewer',
            'role_id'   => $viewerRole->id,
            'is_active' => true,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function makeModule(array $attrs = []): RtmfModule
    {
        return RtmfModule::create(array_merge([
            'code'       => 'TST',
            'name'       => 'Test Module',
            'sort_order' => 10,
        ], $attrs));
    }

    private function makeSub(RtmfModule $module, array $attrs = []): RtmfSubModule
    {
        return RtmfSubModule::create(array_merge([
            'module_id'  => $module->id,
            'code'       => 'S' . uniqid(),
            'name'       => 'Sub ' . uniqid(),
            'sort_order' => 10,
        ], $attrs));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Module CRUD
    // ─────────────────────────────────────────────────────────────────────────

    public function test_admin_can_list_modules(): void
    {
        $before = RtmfModule::count();

        $this->makeModule(['code' => 'MOD1']);
        $this->makeModule(['code' => 'MOD2']);

        $res = $this->actingAs($this->admin)->getJson('/api/rtmf-modules');

        $res->assertOk()->assertJsonCount($before + 2, 'data');
    }

    public function test_admin_can_create_module(): void
    {
        $res = $this->actingAs($this->admin)->postJson('/api/rtmf-modules', [
            'code'       => 'NEW',
            'name'       => 'New Module',
            'sort_order' => 5,
        ]);

        $res->assertOk()->assertJsonPath('data.code', 'NEW');
        $this->assertDatabaseHas('rtmf_modules', ['code' => 'NEW']);
    }

    public function test_viewer_cannot_create_module(): void
    {
        $this->actingAs($this->viewer)
            ->postJson('/api/rtmf-modules', ['code' => 'X', 'name' => 'X'])
            ->assertStatus(403);
    }

    public function test_unauthenticated_cannot_access_modules(): void
    {
        $this->getJson('/api/rtmf-modules')->assertStatus(401);
    }

    public function test_admin_can_update_module(): void
    {
        $module = $this->makeModule();

        $res = $this->actingAs($this->admin)
            ->putJson("/api/rtmf-modules/{$module->id}", ['name' => 'Updated Name']);

        $res->assertOk()->assertJsonPath('data.name', 'Updated Name');
    }

    public function test_admin_can_soft_delete_module(): void
    {
        $module = $this->makeModule();

        $this->actingAs($this->admin)
            ->deleteJson("/api/rtmf-modules/{$module->id}")
            ->assertOk();

        $this->assertSoftDeleted('rtmf_modules', ['id' => $module->id]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Sub-module — Tier 1 (no parent)
    // ─────────────────────────────────────────────────────────────────────────

    public function test_admin_can_create_root_sub_module(): void
    {
        $module = $this->makeModule();

        $res = $this->actingAs($this->admin)->postJson("/api/rtmf-modules/{$module->id}/sub-modules", [
            'code' => 'ROOT',
            'name' => 'Root Sub',
        ]);

        $res->assertOk()->assertJsonPath('data.code', 'ROOT');
        $this->assertDatabaseHas('rtmf_sub_modules', [
            'module_id' => $module->id,
            'code'      => 'ROOT',
            'parent_id' => null,
        ]);
    }

    public function test_sub_module_code_must_be_unique_within_module(): void
    {
        $module = $this->makeModule();
        $this->makeSub($module, ['code' => 'DUP']);

        $this->actingAs($this->admin)
            ->postJson("/api/rtmf-modules/{$module->id}/sub-modules", ['code' => 'DUP', 'name' => 'Duplicate'])
            ->assertStatus(422);
    }

    public function test_same_code_allowed_in_different_modules(): void
    {
        $m1 = $this->makeModule(['code' => 'M1']);
        $m2 = $this->makeModule(['code' => 'M2']);
        $this->makeSub($m1, ['code' => 'SHARED']);

        $this->actingAs($this->admin)
            ->postJson("/api/rtmf-modules/{$m2->id}/sub-modules", ['code' => 'SHARED', 'name' => 'Same Code'])
            ->assertOk();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Sub-module — Tier 2 (child of root)
    // ─────────────────────────────────────────────────────────────────────────

    public function test_admin_can_create_child_sub_module(): void
    {
        $module = $this->makeModule();
        $parent = $this->makeSub($module, ['code' => 'PAR']);

        $res = $this->actingAs($this->admin)->postJson("/api/rtmf-modules/{$module->id}/sub-modules", [
            'code'      => 'CHILD',
            'name'      => 'Child Sub',
            'parent_id' => $parent->id,
        ]);

        $res->assertOk();
        $this->assertDatabaseHas('rtmf_sub_modules', [
            'module_id' => $module->id,
            'code'      => 'CHILD',
            'parent_id' => $parent->id,
        ]);
    }

    public function test_index_returns_both_root_and_children(): void
    {
        $module = $this->makeModule();
        $parent = $this->makeSub($module, ['code' => 'PAR']);
        $this->makeSub($module, ['code' => 'CHILD', 'parent_id' => $parent->id]);

        $res = $this->actingAs($this->admin)->getJson("/api/rtmf-modules/{$module->id}/sub-modules");

        $res->assertOk()->assertJsonCount(2, 'data');

        $items = collect($res->json('data'));
        $this->assertTrue($items->contains('code', 'PAR'));
        $this->assertTrue($items->contains('code', 'CHILD'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Sub-module — Tier 3 (grandchild)
    // ─────────────────────────────────────────────────────────────────────────

    public function test_admin_can_create_grandchild_sub_module(): void
    {
        $module = $this->makeModule();
        $parent = $this->makeSub($module, ['code' => 'PAR']);
        $child  = $this->makeSub($module, ['code' => 'CHILD', 'parent_id' => $parent->id]);

        $res = $this->actingAs($this->admin)->postJson("/api/rtmf-modules/{$module->id}/sub-modules", [
            'code'      => 'GRAND',
            'name'      => 'Grandchild Sub',
            'parent_id' => $child->id,
        ]);

        $res->assertOk();
        $this->assertDatabaseHas('rtmf_sub_modules', [
            'module_id' => $module->id,
            'code'      => 'GRAND',
            'parent_id' => $child->id,
        ]);
    }

    public function test_three_tier_hierarchy_has_correct_ancestry(): void
    {
        $module = $this->makeModule();
        $tier1  = $this->makeSub($module, ['code' => 'T1']);
        $tier2  = $this->makeSub($module, ['code' => 'T2', 'parent_id' => $tier1->id]);
        $tier3  = $this->makeSub($module, ['code' => 'T3', 'parent_id' => $tier2->id]);

        $this->assertNull($tier1->fresh()->parent_id);
        $this->assertEquals($tier1->id, $tier2->fresh()->parent_id);
        $this->assertEquals($tier2->id, $tier3->fresh()->parent_id);
        $this->assertEquals($module->id, $tier3->fresh()->module_id);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Bug: cross-module parent_id
    // ─────────────────────────────────────────────────────────────────────────

    public function test_cannot_set_parent_from_different_module(): void
    {
        $m1 = $this->makeModule(['code' => 'M1']);
        $m2 = $this->makeModule(['code' => 'M2']);

        $parentInM1 = $this->makeSub($m1, ['code' => 'PAR']);

        // parent_id belongs to m1 but we're creating under m2 — should be rejected
        $res = $this->actingAs($this->admin)->postJson("/api/rtmf-modules/{$m2->id}/sub-modules", [
            'code'      => 'CROSS',
            'name'      => 'Cross-module child',
            'parent_id' => $parentInM1->id,
        ]);

        $res->assertStatus(422);
        $this->assertDatabaseMissing('rtmf_sub_modules', ['code' => 'CROSS']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Bug: soft-delete parent orphans children
    // ─────────────────────────────────────────────────────────────────────────

    public function test_deleting_parent_nullifies_children_parent_id(): void
    {
        $module = $this->makeModule();
        $parent = $this->makeSub($module, ['code' => 'PAR']);
        $child  = $this->makeSub($module, ['code' => 'CHILD', 'parent_id' => $parent->id]);

        $this->actingAs($this->admin)
            ->deleteJson("/api/rtmf-modules/{$module->id}/sub-modules/{$parent->id}")
            ->assertOk();

        // Child should be detached (parent_id = null), not orphaned
        $this->assertNull($child->fresh()->parent_id);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Sort order
    // ─────────────────────────────────────────────────────────────────────────

    public function test_sort_order_auto_increments_within_same_parent(): void
    {
        $module = $this->makeModule();
        $parent = $this->makeSub($module, ['code' => 'PAR', 'sort_order' => 10]);

        $c1 = $this->actingAs($this->admin)->postJson("/api/rtmf-modules/{$module->id}/sub-modules", [
            'code' => 'C1', 'name' => 'Child 1', 'parent_id' => $parent->id,
        ])->json('data');

        $c2 = $this->actingAs($this->admin)->postJson("/api/rtmf-modules/{$module->id}/sub-modules", [
            'code' => 'C2', 'name' => 'Child 2', 'parent_id' => $parent->id,
        ])->json('data');

        $this->assertGreaterThan($c1['sortOrder'], $c2['sortOrder']);
    }

    public function test_sort_order_is_independent_per_parent(): void
    {
        $module = $this->makeModule();
        $p1     = $this->makeSub($module, ['code' => 'P1', 'sort_order' => 10]);
        $p2     = $this->makeSub($module, ['code' => 'P2', 'sort_order' => 20]);

        $childOfP1 = $this->actingAs($this->admin)->postJson("/api/rtmf-modules/{$module->id}/sub-modules", [
            'code' => 'C1', 'name' => 'Child of P1', 'parent_id' => $p1->id,
        ])->json('data');

        $childOfP2 = $this->actingAs($this->admin)->postJson("/api/rtmf-modules/{$module->id}/sub-modules", [
            'code' => 'C2', 'name' => 'Child of P2', 'parent_id' => $p2->id,
        ])->json('data');

        // Both children start at sort_order = 10 independently
        $this->assertEquals($childOfP1['sortOrder'], $childOfP2['sortOrder']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Reorder
    // ─────────────────────────────────────────────────────────────────────────

    public function test_admin_can_reorder_sub_modules(): void
    {
        $module = $this->makeModule();
        $s1     = $this->makeSub($module, ['code' => 'S1', 'sort_order' => 10]);
        $s2     = $this->makeSub($module, ['code' => 'S2', 'sort_order' => 20]);
        $s3     = $this->makeSub($module, ['code' => 'S3', 'sort_order' => 30]);

        $this->actingAs($this->admin)
            ->postJson("/api/rtmf-modules/{$module->id}/sub-modules/reorder", [
                'ids' => [$s3->id, $s1->id, $s2->id],
            ])
            ->assertOk();

        $this->assertEquals(10, $s3->fresh()->sort_order);
        $this->assertEquals(20, $s1->fresh()->sort_order);
        $this->assertEquals(30, $s2->fresh()->sort_order);
    }

    public function test_reorder_does_not_affect_other_modules(): void
    {
        $m1 = $this->makeModule(['code' => 'M1']);
        $m2 = $this->makeModule(['code' => 'M2']);

        $s1    = $this->makeSub($m1, ['code' => 'S1', 'sort_order' => 10]);
        $s2    = $this->makeSub($m1, ['code' => 'S2', 'sort_order' => 20]);
        $other = $this->makeSub($m2, ['code' => 'OTH', 'sort_order' => 10]);

        $this->actingAs($this->admin)
            ->postJson("/api/rtmf-modules/{$m1->id}/sub-modules/reorder", [
                'ids' => [$s2->id, $s1->id],
            ])
            ->assertOk();

        $this->assertEquals(10, $other->fresh()->sort_order);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Tier depth limit (max 8)
    // ─────────────────────────────────────────────────────────────────────────

    public function test_can_create_sub_module_at_tier_8(): void
    {
        $module = $this->makeModule();

        $current = $this->makeSub($module, ['code' => 'T1']);
        for ($i = 2; $i <= 7; $i++) {
            $current = $this->makeSub($module, ['code' => "T{$i}", 'parent_id' => $current->id]);
        }

        // Tier 8 — should succeed
        $res = $this->actingAs($this->admin)->postJson("/api/rtmf-modules/{$module->id}/sub-modules", [
            'code'      => 'T8',
            'name'      => 'Tier 8',
            'parent_id' => $current->id,
        ]);

        $res->assertOk();
    }

    public function test_cannot_create_sub_module_beyond_tier_8(): void
    {
        $module = $this->makeModule();

        $current = $this->makeSub($module, ['code' => 'T1']);
        for ($i = 2; $i <= 8; $i++) {
            $current = $this->makeSub($module, ['code' => "T{$i}", 'parent_id' => $current->id]);
        }

        // Tier 9 — should be rejected
        $res = $this->actingAs($this->admin)->postJson("/api/rtmf-modules/{$module->id}/sub-modules", [
            'code'      => 'T9',
            'name'      => 'Tier 9',
            'parent_id' => $current->id,
        ]);

        $res->assertStatus(422);
        $res->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }
}
