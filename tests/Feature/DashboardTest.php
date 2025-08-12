<?php

namespace Tests\Feature;

use App\Models\CostEstimate;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                        ->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($assert) => $assert
            ->component('dashboard')
            ->has('stats')
            ->has('recentPurchaseOrders')
            ->has('pendingApprovals')
            ->has('charts')
            ->has('userPermissions')
        );
    }

    public function test_dashboard_shows_statistics(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
                        ->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($assert) => $assert
            ->has('stats.total_purchase_orders')
            ->has('stats.pending_validation')
            ->has('stats.in_progress') 
            ->has('stats.completed')
            ->has('stats.total_cost_estimates')
            ->has('stats.pending_approval')
        );
    }

    public function test_bsp_user_sees_pending_validation_in_dashboard(): void
    {
        $bsp = User::factory()->bsp()->create();
        $po = PurchaseOrder::factory()->pendingValidation()->create();

        $response = $this->actingAs($bsp)
                        ->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($assert) => $assert
            ->where('userPermissions.canValidatePO', true)
            ->has('pendingApprovals.purchase_orders')
        );
    }

    public function test_dau_user_sees_pending_cost_estimate_approvals_in_dashboard(): void
    {
        $dau = User::factory()->dau()->create();
        $ce = CostEstimate::factory()->pendingApproval()->create();

        $response = $this->actingAs($dau)
                        ->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($assert) => $assert
            ->where('userPermissions.canApproveCE', true)
            ->has('pendingApprovals.cost_estimates')
        );
    }

    public function test_superadmin_sees_user_management_permissions(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $response = $this->actingAs($superadmin)
                        ->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($assert) => $assert
            ->where('userPermissions.canManageUsers', true)
            ->where('userPermissions.canValidatePO', true)
            ->where('userPermissions.canApproveCE', true)
            ->where('userPermissions.canCreateCE', true)
            ->where('userPermissions.canCompletePO', true)
            ->has('stats.total_users')
        );
    }

    public function test_regular_user_has_limited_permissions(): void
    {
        $user = User::factory()->create(['role' => 'unit_kerja']);

        $response = $this->actingAs($user)
                        ->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($assert) => $assert
            ->where('userPermissions.canManageUsers', false)
            ->where('userPermissions.canValidatePO', false)
            ->where('userPermissions.canApproveCE', false)
            ->where('userPermissions.canCreateCE', false)
            ->where('userPermissions.canCompletePO', false)
        );
    }

    public function test_dashboard_shows_recent_purchase_orders(): void
    {
        // Clear existing data from seeder
        PurchaseOrder::query()->delete();
        
        $user = User::factory()->create();
        
        // Create some purchase orders
        $recentPO = PurchaseOrder::factory()->create([
            'created_at' => now()->subHours(1),
            'title' => 'Recent Purchase Order'
        ]);

        $response = $this->actingAs($user)
                        ->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($assert) => $assert
            ->has('recentPurchaseOrders', fn ($assert) => $assert
                ->where('0.title', 'Recent Purchase Order')
                ->etc()
            )
        );
    }

    public function test_unauthenticated_user_cannot_access_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }
}