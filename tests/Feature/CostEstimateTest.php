<?php

namespace Tests\Feature;

use App\Models\CostEstimate;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CostEstimateTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_authenticated_user_can_view_cost_estimates_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                        ->get('/cost-estimates');

        $response->assertStatus(200);
        $response->assertInertia(fn ($assert) => $assert->component('cost-estimates/index'));
    }

    public function test_bsp_user_can_create_cost_estimate(): void
    {
        $bspUser = User::factory()->bsp()->create();
        $purchaseOrder = PurchaseOrder::factory()->validated()->create();

        $data = [
            'purchase_order_id' => $purchaseOrder->id,
            'title' => 'Test Cost Estimate',
            'description' => 'This is a test cost estimate',
            'type' => 'cost_estimate',
            'items' => [
                [
                    'description' => 'Item 1',
                    'unit' => 'pcs',
                    'quantity' => 10,
                    'unit_price' => 100,
                    'item_code' => 'ITM001',
                    'notes' => 'Test item',
                ],
                [
                    'description' => 'Item 2',
                    'unit' => 'kg',
                    'quantity' => 5,
                    'unit_price' => 50,
                    'item_code' => null,
                    'notes' => null,
                ],
            ],
        ];

        $response = $this->actingAs($bspUser)
                        ->post('/cost-estimates', $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('cost_estimates', [
            'title' => 'Test Cost Estimate',
            'purchase_order_id' => $purchaseOrder->id,
            'created_by' => $bspUser->id,
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('cost_estimate_items', [
            'description' => 'Item 1',
            'quantity' => 10,
            'unit_price' => 100,
            'total_price' => 1000,
        ]);
    }

    public function test_dau_user_permissions(): void
    {
        $dauUser = User::factory()->dau()->create();
        
        $this->assertTrue($dauUser->canApproveCostEstimates());
        $this->assertFalse($dauUser->canCreateCostEstimates());
        $this->assertFalse($dauUser->canValidatePurchaseOrders());
    }

    public function test_cost_estimate_approval_workflow(): void
    {
        $costEstimate = CostEstimate::factory()->draft()->create();
        
        $this->assertTrue($costEstimate->canBeApproved());
        $this->assertTrue($costEstimate->canBeRejected());

        $costEstimate->update(['status' => 'approved']);
        $this->assertFalse($costEstimate->canBeApproved());
        $this->assertFalse($costEstimate->canBeRejected());
    }

    public function test_cost_estimate_number_generation(): void
    {
        $ceNumber = CostEstimate::generateCeNumber();
        $currentYear = now()->year;
        
        $this->assertStringStartsWith("CE-{$currentYear}-", $ceNumber);
        $this->assertMatchesRegularExpression('/^CE-\d{4}-\d{4}$/', $ceNumber);
    }

    public function test_user_cannot_create_cost_estimate_without_permission(): void
    {
        $user = User::factory()->create(['role' => 'unit_kerja']);
        $purchaseOrder = PurchaseOrder::factory()->validated()->create();

        $data = [
            'purchase_order_id' => $purchaseOrder->id,
            'title' => 'Test Cost Estimate',
            'type' => 'cost_estimate',
            'items' => [
                [
                    'description' => 'Item 1',
                    'unit' => 'pcs',
                    'quantity' => 10,
                    'unit_price' => 100,
                ],
            ],
        ];

        $response = $this->actingAs($user)
                        ->post('/cost-estimates', $data);

        $response->assertStatus(403);
    }

    public function test_bsp_user_cannot_approve_cost_estimates(): void
    {
        $bspUser = User::factory()->bsp()->create();
        
        $this->assertFalse($bspUser->canApproveCostEstimates());
        $this->assertTrue($bspUser->canCreateCostEstimates());
    }

    public function test_cost_estimate_total_calculation(): void
    {
        $costEstimate = CostEstimate::factory()->create(['total_amount' => 0]);
        
        // Create items
        $costEstimate->items()->create([
            'description' => 'Item 1',
            'unit' => 'pcs',
            'quantity' => 10,
            'unit_price' => 100,
            'total_price' => 1000,
            'sort_order' => 0,
        ]);

        $costEstimate->items()->create([
            'description' => 'Item 2',
            'unit' => 'kg',
            'quantity' => 5,
            'unit_price' => 50,
            'total_price' => 250,
            'sort_order' => 1,
        ]);

        $costEstimate->calculateTotalAmount();

        $this->assertEquals(1250, $costEstimate->fresh()->total_amount);
    }
}