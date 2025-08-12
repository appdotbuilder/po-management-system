<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_superadmin_can_view_users_index(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $response = $this->actingAs($superadmin)
                        ->get('/users');

        $response->assertStatus(200);
        $response->assertInertia(fn ($assert) => $assert->component('users/index'));
    }

    public function test_non_superadmin_cannot_view_users_index(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)
                        ->get('/users');

        $response->assertStatus(403);
    }

    public function test_superadmin_can_create_user(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $data = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'bsp',
            'is_active' => true,
        ];

        $response = $this->actingAs($superadmin)
                        ->post('/users', $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'role' => 'bsp',
            'is_active' => true,
        ]);

        $user = User::where('email', 'newuser@example.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_superadmin_can_update_user(): void
    {
        $superadmin = User::factory()->superadmin()->create();
        $user = User::factory()->create(['role' => 'unit_kerja']);

        $data = [
            'name' => 'Updated User',
            'email' => $user->email,
            'role' => 'bsp',
            'is_active' => false,
        ];

        $response = $this->actingAs($superadmin)
                        ->put("/users/{$user->id}", $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated User',
            'role' => 'bsp',
            'is_active' => false,
        ]);
    }

    public function test_superadmin_can_deactivate_user_via_update(): void
    {
        $superadmin = User::factory()->superadmin()->create();
        $user = User::factory()->create(['is_active' => true]);

        $data = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'is_active' => false,
        ];

        $response = $this->actingAs($superadmin)
                        ->put("/users/{$user->id}", $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_active' => false,
        ]);
    }

    public function test_user_role_permission_methods(): void
    {
        $superadmin = User::factory()->superadmin()->create();
        $admin = User::factory()->admin()->create();
        $bsp = User::factory()->bsp()->create();
        $dau = User::factory()->dau()->create();
        $unitKerja = User::factory()->create(['role' => 'unit_kerja']);

        // Test canManageUsers
        $this->assertTrue($superadmin->canManageUsers());
        $this->assertFalse($admin->canManageUsers());
        $this->assertFalse($bsp->canManageUsers());

        // Test canValidatePurchaseOrders
        $this->assertTrue($superadmin->canValidatePurchaseOrders());
        $this->assertTrue($admin->canValidatePurchaseOrders());
        $this->assertTrue($bsp->canValidatePurchaseOrders());
        $this->assertFalse($unitKerja->canValidatePurchaseOrders());

        // Test canApproveCostEstimates
        $this->assertTrue($superadmin->canApproveCostEstimates());
        $this->assertTrue($admin->canApproveCostEstimates());
        $this->assertTrue($dau->canApproveCostEstimates());
        $this->assertFalse($bsp->canApproveCostEstimates());

        // Test canCreateCostEstimates
        $this->assertTrue($superadmin->canCreateCostEstimates());
        $this->assertTrue($admin->canCreateCostEstimates());
        $this->assertTrue($bsp->canCreateCostEstimates());
        $this->assertFalse($dau->canCreateCostEstimates());

        // Test canCompletePurchaseOrders
        $this->assertTrue($superadmin->canCompletePurchaseOrders());
        $this->assertTrue($admin->canCompletePurchaseOrders());
        $this->assertFalse($bsp->canCompletePurchaseOrders());
        $this->assertFalse($dau->canCompletePurchaseOrders());
    }

    public function test_user_role_display_names(): void
    {
        $superadmin = User::factory()->superadmin()->create();
        $bsp = User::factory()->bsp()->create();
        $unitKerja = User::factory()->create(['role' => 'unit_kerja']);

        $this->assertEquals('Super Administrator', $superadmin->getRoleDisplayName());
        $this->assertEquals('BSP', $bsp->getRoleDisplayName());
        $this->assertEquals('Unit Kerja', $unitKerja->getRoleDisplayName());
    }
}