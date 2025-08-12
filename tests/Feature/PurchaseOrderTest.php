<?php

namespace Tests\Feature;

use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PurchaseOrderTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_authenticated_user_can_view_purchase_orders_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                        ->get('/purchase-orders');

        $response->assertStatus(200);
        $response->assertInertia(fn ($assert) => $assert->component('purchase-orders/index'));
    }

    public function test_user_can_create_purchase_order(): void
    {
        $user = User::factory()->create();

        $data = [
            'title' => 'Test Purchase Order',
            'description' => 'This is a test purchase order',
            'estimated_value' => 10000.00,
            'priority' => 'medium',
            'required_by' => now()->addDays(30)->format('Y-m-d'),
        ];

        $response = $this->actingAs($user)
                        ->post('/purchase-orders', $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('purchase_orders', [
            'title' => 'Test Purchase Order',
            'created_by' => $user->id,
            'status' => 'draft',
        ]);
    }

    public function test_bsp_user_permissions(): void
    {
        $bspUser = User::factory()->bsp()->create();
        
        $this->assertTrue($bspUser->canValidatePurchaseOrders());
        $this->assertTrue($bspUser->canCreateCostEstimates());
        $this->assertFalse($bspUser->canManageUsers());
    }

    public function test_admin_user_permissions(): void
    {
        $adminUser = User::factory()->admin()->create();
        
        $this->assertTrue($adminUser->canValidatePurchaseOrders());
        $this->assertTrue($adminUser->canCompletePurchaseOrders());
        $this->assertTrue($adminUser->canApproveCostEstimates());
        $this->assertFalse($adminUser->canManageUsers());
    }

    public function test_purchase_order_number_generation(): void
    {
        $poNumber = PurchaseOrder::generatePoNumber();
        $currentYear = now()->year;
        
        $this->assertStringStartsWith("PO-{$currentYear}-", $poNumber);
        $this->assertMatchesRegularExpression('/^PO-\d{4}-\d{4}$/', $poNumber);
    }

    public function test_user_can_view_purchase_order_details(): void
    {
        $user = User::factory()->create();
        $purchaseOrder = PurchaseOrder::factory()->create();

        $response = $this->actingAs($user)
                        ->get("/purchase-orders/{$purchaseOrder->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($assert) => $assert->component('purchase-orders/show'));
    }

    public function test_user_can_update_purchase_order(): void
    {
        $user = User::factory()->create();
        $purchaseOrder = PurchaseOrder::factory()->draft()->create(['created_by' => $user->id]);

        $data = [
            'title' => 'Updated Purchase Order',
            'description' => 'This is an updated purchase order',
            'estimated_value' => 15000.00,
            'priority' => 'high',
            'required_by' => now()->addDays(60)->format('Y-m-d'),
        ];

        $response = $this->actingAs($user)
                        ->put("/purchase-orders/{$purchaseOrder->id}", $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('purchase_orders', [
            'id' => $purchaseOrder->id,
            'title' => 'Updated Purchase Order',
            'estimated_value' => 15000.00,
            'priority' => 'high',
        ]);
    }

    public function test_purchase_order_status_workflow(): void
    {
        $purchaseOrder = PurchaseOrder::factory()->draft()->create();
        
        $this->assertTrue($purchaseOrder->canBeValidated());
        $this->assertFalse($purchaseOrder->canHaveCostEstimate());
        $this->assertFalse($purchaseOrder->canBeCompleted());

        $purchaseOrder->update(['status' => 'validated']);
        $this->assertFalse($purchaseOrder->canBeValidated());
        $this->assertTrue($purchaseOrder->canHaveCostEstimate());
        $this->assertFalse($purchaseOrder->canBeCompleted());

        $purchaseOrder->update(['status' => 'in_progress']);
        $this->assertFalse($purchaseOrder->canBeValidated());
        $this->assertFalse($purchaseOrder->canHaveCostEstimate());
        $this->assertTrue($purchaseOrder->canBeCompleted());
    }
}