<?php

namespace Database\Seeders;

use App\Models\CostEstimate;
use App\Models\CostEstimateItem;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PurchaseOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bspUsers = User::where('role', 'bsp')->get();
        $dauUsers = User::where('role', 'dau')->get();
        $adminUsers = User::where('role', 'admin')->get();
        $allUsers = User::all();

        // Create draft purchase orders
        PurchaseOrder::factory(5)
            ->draft()
            ->create([
                'created_by' => $allUsers->random()->id,
            ]);

        // Create pending validation purchase orders
        PurchaseOrder::factory(3)
            ->pendingValidation()
            ->create([
                'created_by' => $allUsers->random()->id,
            ]);

        // Create validated purchase orders
        $validatedPOs = PurchaseOrder::factory(4)
            ->validated()
            ->create([
                'created_by' => $allUsers->random()->id,
                'validated_by' => $bspUsers->isNotEmpty() ? $bspUsers->random()->id : null,
            ]);

        // Create in progress purchase orders with cost estimates
        $inProgressPOs = PurchaseOrder::factory(6)
            ->inProgress()
            ->create([
                'created_by' => $allUsers->random()->id,
                'validated_by' => $bspUsers->isNotEmpty() ? $bspUsers->random()->id : null,
            ]);

        // Create completed purchase orders
        PurchaseOrder::factory(8)
            ->completed()
            ->create([
                'created_by' => $allUsers->random()->id,
                'validated_by' => $bspUsers->isNotEmpty() ? $bspUsers->random()->id : null,
                'completed_by' => $adminUsers->isNotEmpty() ? $adminUsers->random()->id : null,
            ]);

        // Create cost estimates for some validated and in-progress POs
        $poisForCostEstimates = $validatedPOs->concat($inProgressPOs);

        foreach ($poisForCostEstimates as $po) {
            $costEstimate = CostEstimate::factory()->create([
                'purchase_order_id' => $po->id,
                'created_by' => $bspUsers->isNotEmpty() ? $bspUsers->random()->id : $allUsers->random()->id,
                'status' => $po->status === 'in_progress' ? 'approved' : 'draft',
                'approved_by' => $po->status === 'in_progress' && $dauUsers->isNotEmpty() ? $dauUsers->random()->id : null,
                'approved_at' => $po->status === 'in_progress' ? now()->subDays(random_int(1, 7)) : null,
            ]);

            // Create cost estimate items
            $itemsCount = random_int(3, 8);
            for ($i = 0; $i < $itemsCount; $i++) {
                CostEstimateItem::factory()->create([
                    'cost_estimate_id' => $costEstimate->id,
                    'sort_order' => $i,
                ]);
            }

            // Recalculate total amount
            $costEstimate->calculateTotalAmount();
        }

        // Create some pending approval cost estimates
        CostEstimate::factory(3)
            ->pendingApproval()
            ->create([
                'purchase_order_id' => PurchaseOrder::where('status', 'ce_boq_created')->inRandomOrder()->first()?->id ?: ($validatedPOs->first()?->id ?: PurchaseOrder::factory()->validated()->create()->id),
                'created_by' => $bspUsers->isNotEmpty() ? $bspUsers->random()->id : $allUsers->random()->id,
            ])
            ->each(function ($costEstimate) {
                // Create items for pending approval cost estimates
                $itemsCount = random_int(2, 5);
                for ($i = 0; $i < $itemsCount; $i++) {
                    CostEstimateItem::factory()->create([
                        'cost_estimate_id' => $costEstimate->id,
                        'sort_order' => $i,
                    ]);
                }
                $costEstimate->calculateTotalAmount();
            });
    }
}