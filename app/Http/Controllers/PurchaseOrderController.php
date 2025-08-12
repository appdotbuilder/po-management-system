<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseOrderRequest;
use App\Http\Requests\UpdatePurchaseOrderRequest;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['createdBy', 'validatedBy', 'completedBy'])
                             ->latest();

        // Apply filters
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('priority') && $request->priority !== '') {
            $query->where('priority', $request->priority);
        }

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $purchaseOrders = $query->paginate(15);

        return Inertia::render('purchase-orders/index', [
            'purchaseOrders' => $purchaseOrders,
            'filters' => $request->only(['status', 'priority', 'search']),
            'can' => [
                'create' => true,
                'validate' => auth()->user()->canValidatePurchaseOrders(),
                'complete' => auth()->user()->canCompletePurchaseOrders(),
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('purchase-orders/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePurchaseOrderRequest $request)
    {
        $purchaseOrder = PurchaseOrder::create([
            ...$request->validated(),
            'po_number' => PurchaseOrder::generatePoNumber(),
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'createdBy',
            'validatedBy',
            'completedBy',
            'costEstimates.createdBy',
            'costEstimates.approvedBy'
        ]);

        return Inertia::render('purchase-orders/show', [
            'purchaseOrder' => $purchaseOrder,
            'can' => [
                'edit' => true,
                'delete' => true,
                'validate' => auth()->user()->canValidatePurchaseOrders() && $purchaseOrder->canBeValidated(),
                'complete' => auth()->user()->canCompletePurchaseOrders() && $purchaseOrder->canBeCompleted(),
                'createCostEstimate' => auth()->user()->canCreateCostEstimates() && $purchaseOrder->canHaveCostEstimate(),
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PurchaseOrder $purchaseOrder)
    {
        return Inertia::render('purchase-orders/edit', [
            'purchaseOrder' => $purchaseOrder
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->update($request->validated());

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if (!in_array($purchaseOrder->status, ['draft', 'pending_validation'])) {
            return redirect()->route('purchase-orders.index')
                ->with('error', 'Cannot delete purchase order with current status.');
        }

        $purchaseOrder->delete();

        return redirect()->route('purchase-orders.index')
            ->with('success', 'Purchase order deleted successfully.');
    }


}