<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CostEstimate;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        $user = auth()->user();

        // Get basic statistics
        $stats = [
            'total_purchase_orders' => PurchaseOrder::count(),
            'pending_validation' => PurchaseOrder::where('status', 'pending_validation')->count(),
            'in_progress' => PurchaseOrder::where('status', 'in_progress')->count(),
            'completed' => PurchaseOrder::where('status', 'completed')->count(),
            'total_cost_estimates' => CostEstimate::count(),
            'pending_approval' => CostEstimate::where('status', 'pending_approval')->count(),
            'total_users' => $user->canManageUsers() ? User::active()->count() : null,
        ];

        // Get recent purchase orders
        $recentPurchaseOrders = PurchaseOrder::with(['createdBy'])
                                            ->latest()
                                            ->limit(5)
                                            ->get();

        // Get pending approvals for current user
        $pendingApprovals = [];
        if ($user->canValidatePurchaseOrders()) {
            $pendingApprovals['purchase_orders'] = PurchaseOrder::where('status', 'pending_validation')
                                                               ->with('createdBy')
                                                               ->limit(5)
                                                               ->get();
        }

        if ($user->canApproveCostEstimates()) {
            $pendingApprovals['cost_estimates'] = CostEstimate::where('status', 'pending_approval')
                                                             ->with(['purchaseOrder', 'createdBy'])
                                                             ->limit(5)
                                                             ->get();
        }

        // Get status distribution for charts
        $purchaseOrderStatuses = PurchaseOrder::select('status', DB::raw('count(*) as count'))
                                              ->groupBy('status')
                                              ->get()
                                              ->pluck('count', 'status');

        $priorityDistribution = PurchaseOrder::select('priority', DB::raw('count(*) as count'))
                                            ->groupBy('priority')
                                            ->get()
                                            ->pluck('count', 'priority');

        return Inertia::render('dashboard', [
            'stats' => $stats,
            'recentPurchaseOrders' => $recentPurchaseOrders,
            'pendingApprovals' => $pendingApprovals,
            'charts' => [
                'purchaseOrderStatuses' => $purchaseOrderStatuses,
                'priorityDistribution' => $priorityDistribution,
            ],
            'userPermissions' => [
                'canManageUsers' => $user->canManageUsers(),
                'canValidatePO' => $user->canValidatePurchaseOrders(),
                'canApproveCE' => $user->canApproveCostEstimates(),
                'canCreateCE' => $user->canCreateCostEstimates(),
                'canCompletePO' => $user->canCompletePurchaseOrders(),
            ]
        ]);
    }
}