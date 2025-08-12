<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCostEstimateRequest;
use App\Http\Requests\UpdateCostEstimateRequest;
use App\Models\CostEstimate;
use App\Models\CostEstimateItem;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CostEstimateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = CostEstimate::with(['purchaseOrder', 'createdBy', 'approvedBy'])
                            ->latest();

        // Apply filters
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('type') && $request->type !== '') {
            $query->where('type', $request->type);
        }

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ce_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhereHas('purchaseOrder', function ($pq) use ($search) {
                      $pq->where('po_number', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%");
                  });
            });
        }

        $costEstimates = $query->paginate(15);

        return Inertia::render('cost-estimates/index', [
            'costEstimates' => $costEstimates,
            'filters' => $request->only(['status', 'type', 'search']),
            'can' => [
                'create' => auth()->user()->canCreateCostEstimates(),
                'approve' => auth()->user()->canApproveCostEstimates(),
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        if (!auth()->user()->canCreateCostEstimates()) {
            abort(403, 'Unauthorized to create cost estimates.');
        }

        $purchaseOrder = null;
        if ($request->has('purchase_order_id')) {
            $purchaseOrder = PurchaseOrder::findOrFail($request->purchase_order_id);
            if (!$purchaseOrder->canHaveCostEstimate()) {
                return redirect()->route('purchase-orders.show', $purchaseOrder)
                    ->with('error', 'Purchase order is not validated yet.');
            }
        }

        $validatedPurchaseOrders = PurchaseOrder::where('status', 'validated')
                                               ->with('createdBy')
                                               ->get();

        return Inertia::render('cost-estimates/create', [
            'purchaseOrder' => $purchaseOrder,
            'validatedPurchaseOrders' => $validatedPurchaseOrders,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCostEstimateRequest $request)
    {
        if (!auth()->user()->canCreateCostEstimates()) {
            abort(403, 'Unauthorized to create cost estimates.');
        }

        $purchaseOrder = PurchaseOrder::findOrFail($request->purchase_order_id);
        if (!$purchaseOrder->canHaveCostEstimate()) {
            return redirect()->back()
                ->with('error', 'Purchase order is not validated yet.');
        }

        DB::transaction(function () use ($request, $purchaseOrder) {
            $costEstimate = CostEstimate::create([
                'purchase_order_id' => $request->purchase_order_id,
                'ce_number' => CostEstimate::generateCeNumber(),
                'title' => $request->title,
                'description' => $request->description,
                'type' => $request->type,
                'created_by' => auth()->id(),
            ]);

            foreach ($request->items as $index => $item) {
                CostEstimateItem::create([
                    'cost_estimate_id' => $costEstimate->id,
                    'item_code' => $item['item_code'],
                    'description' => $item['description'],
                    'unit' => $item['unit'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'notes' => $item['notes'],
                    'sort_order' => $index,
                ]);
            }

            // Update purchase order status
            $purchaseOrder->update(['status' => 'ce_boq_created']);
        });

        $costEstimate = CostEstimate::where('purchase_order_id', $request->purchase_order_id)
                                   ->latest()
                                   ->first();

        return redirect()->route('cost-estimates.show', $costEstimate)
            ->with('success', 'Cost estimate created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CostEstimate $costEstimate)
    {
        $costEstimate->load([
            'purchaseOrder.createdBy',
            'createdBy',
            'approvedBy',
            'items'
        ]);

        return Inertia::render('cost-estimates/show', [
            'costEstimate' => $costEstimate,
            'can' => [
                'edit' => auth()->user()->canCreateCostEstimates() && $costEstimate->status === 'draft',
                'delete' => auth()->user()->canCreateCostEstimates() && $costEstimate->status === 'draft',
                'approve' => auth()->user()->canApproveCostEstimates() && $costEstimate->canBeApproved(),
                'reject' => auth()->user()->canApproveCostEstimates() && $costEstimate->canBeRejected(),
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CostEstimate $costEstimate)
    {
        if (!auth()->user()->canCreateCostEstimates() || $costEstimate->status !== 'draft') {
            abort(403, 'Cannot edit this cost estimate.');
        }

        $costEstimate->load(['purchaseOrder', 'items']);

        return Inertia::render('cost-estimates/edit', [
            'costEstimate' => $costEstimate
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCostEstimateRequest $request, CostEstimate $costEstimate)
    {
        if (!auth()->user()->canCreateCostEstimates() || $costEstimate->status !== 'draft') {
            abort(403, 'Cannot edit this cost estimate.');
        }

        DB::transaction(function () use ($request, $costEstimate) {
            $costEstimate->update([
                'title' => $request->title,
                'description' => $request->description,
                'type' => $request->type,
            ]);

            // Delete existing items
            $costEstimate->items()->delete();

            // Create new items
            foreach ($request->items as $index => $item) {
                CostEstimateItem::create([
                    'cost_estimate_id' => $costEstimate->id,
                    'item_code' => $item['item_code'],
                    'description' => $item['description'],
                    'unit' => $item['unit'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'notes' => $item['notes'],
                    'sort_order' => $index,
                ]);
            }
        });

        return redirect()->route('cost-estimates.show', $costEstimate)
            ->with('success', 'Cost estimate updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CostEstimate $costEstimate)
    {
        if (!auth()->user()->canCreateCostEstimates() || $costEstimate->status !== 'draft') {
            abort(403, 'Cannot delete this cost estimate.');
        }

        // Update purchase order status back to validated
        $costEstimate->purchaseOrder->update(['status' => 'validated']);

        $costEstimate->delete();

        return redirect()->route('cost-estimates.index')
            ->with('success', 'Cost estimate deleted successfully.');
    }


}