<?php

namespace Tests\Feature;

use App\Models\User;
use BladeUI\Icons\Factory as IconFactory;
use BladeUI\Icons\IconsManifest;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ViewErrorBag;
use Mockery;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class IconErrorTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_layout_renders_without_icon_error(): void
    {
        // Create user with permission to see coach section
        $user = User::factory()->create(['is_active' => true]);
        $permission = Permission::findByName('view_member_section', 'web');
        $manageTeamsPermission = Permission::findByName('manage_teams', 'web');
        $user->givePermissionTo($permission);
        $user->givePermissionTo($manageTeamsPermission);

        $response = $this->actingAs($user)->get('/clenska-sekce/profil');

        $response->assertStatus(200);
    }

    public function test_heroicon_o_users_is_valid(): void
    {
        $view = $this->blade('<x-icon name="heroicon-o-users" class="w-5 h-5" />');
        $view->assertSee('svg');
    }

    public function test_heroicon_o_user_group_is_valid(): void
    {
        $view = $this->blade('<x-icon name="heroicon-o-user-group" class="w-5 h-5" />');
        $view->assertSee('svg');
    }

    public function test_other_common_icons_are_valid(): void
    {
        $icons = [
            'heroicon-o-academic-cap',
            'heroicon-o-trophy',
            'heroicon-o-star',
            'heroicon-o-clock',
            'heroicon-o-map-pin',
            'heroicon-o-ellipsis-horizontal',
            'heroicon-o-banknotes',
            'heroicon-o-check-badge',
            'heroicon-o-arrow-down-left',
            'heroicon-o-check',
            'heroicon-o-qr-code',
            'heroicon-o-chevron-right',
            'heroicon-o-home',
            'heroicon-o-calendar-days',
            'heroicon-o-user',
            'heroicon-o-credit-card',
            'heroicon-o-chart-bar',
        ];

        foreach ($icons as $icon) {
            $view = $this->blade('<x-icon :name="$icon" class="w-5 h-5" />', ['icon' => $icon]);
            $view->assertSee('svg');
        }
    }

    public function test_icons_render_without_registering_the_entire_icon_library(): void
    {
        // A missing manifest must never trigger a filesystem scan, even when
        // the package registers components again or SVGs are first rendered.
        $filesystem = Mockery::mock(Filesystem::class);
        $filesystem->shouldNotReceive('exists');
        $filesystem->shouldNotReceive('allFiles');
        app()->instance(IconsManifest::class, new IconsManifest($filesystem, '/unused-icon-manifest.php'));
        app()->forgetInstance(IconFactory::class);
        app(IconFactory::class)->registerComponents();

        $aliases = Blade::getClassComponentAliases();
        $this->assertArrayHasKey('icon', $aliases);
        $this->assertEmpty(array_filter(array_keys($aliases), fn (string $alias) => str_starts_with($alias, 'heroicon-') || preg_match('/^fa[bldrst]-/', $alias)
        ));

        $this->blade('<x-filament::icon icon="heroicon-o-user" class="auth-icon" />')
            ->assertSee('<svg', false)
            ->assertSee('auth-icon');
        $this->blade('<x-filament::icon icon="fas-users" class="admin-icon" />')
            ->assertSee('<svg', false)
            ->assertSee('admin-icon');
    }

    public function test_economy_history_and_kpi_icons_render_without_named_aliases(): void
    {
        $this->actingAs($this->createMember());

        $this->view('member.economy.index', [
            'errors' => new ViewErrorBag,
            'summary' => ['total_to_pay' => 0, 'overdue_amount' => 0, 'paid_total' => 100],
            'openCharges' => collect(),
            'recentPayments' => collect([(object) ['amount' => 100, 'variable_symbol' => null]]),
            'paidCharges' => collect([(object) ['title' => 'Test payment', 'updated_at' => now()]]),
        ])->assertSee('Test payment')->assertSee('<svg', false);
    }
}
