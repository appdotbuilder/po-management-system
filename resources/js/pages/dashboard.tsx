import { Head, Link } from '@inertiajs/react';
import { AppLayout } from '@/components/app-layout';
import { type BreadcrumbItem, type SharedData } from '@/types';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    is_active: boolean;
    created_at: string;
}

interface PurchaseOrder {
    id: number;
    po_number: string;
    title: string;
    status: string;
    priority: string;
    created_by: User;
    created_at: string;
}

interface CostEstimate {
    id: number;
    ce_number: string;
    title: string;
    status: string;
    purchase_order: PurchaseOrder;
    created_by: User;
    created_at: string;
}

interface DashboardStats {
    total_purchase_orders: number;
    pending_validation: number;
    in_progress: number;
    completed: number;
    total_cost_estimates: number;
    pending_approval: number;
    total_users?: number;
}

interface PendingApprovals {
    purchase_orders?: PurchaseOrder[];
    cost_estimates?: CostEstimate[];
}

interface Charts {
    purchaseOrderStatuses: Record<string, number>;
    priorityDistribution: Record<string, number>;
}

interface UserPermissions {
    canManageUsers: boolean;
    canValidatePO: boolean;
    canApproveCE: boolean;
    canCreateCE: boolean;
    canCompletePO: boolean;
}

interface Props extends SharedData {
    stats: DashboardStats;
    recentPurchaseOrders: PurchaseOrder[];
    pendingApprovals: PendingApprovals;
    charts: Charts;
    userPermissions: UserPermissions;
    [key: string]: unknown;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const statusColors: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-800',
    pending_validation: 'bg-yellow-100 text-yellow-800',
    validated: 'bg-blue-100 text-blue-800',
    pending_ce_boq: 'bg-orange-100 text-orange-800',
    ce_boq_created: 'bg-indigo-100 text-indigo-800',
    ce_boq_approved: 'bg-purple-100 text-purple-800',
    in_progress: 'bg-blue-100 text-blue-800',
    completed: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
    pending_approval: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
};

const priorityColors: Record<string, string> = {
    low: 'bg-green-100 text-green-800',
    medium: 'bg-yellow-100 text-yellow-800',
    high: 'bg-orange-100 text-orange-800',
    urgent: 'bg-red-100 text-red-800',
};

export default function Dashboard({
    stats,
    recentPurchaseOrders,
    pendingApprovals,
    charts,
    userPermissions
}: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            
            <div className="space-y-6">
                {/* Header */}
                <div>
                    <h1 className="text-2xl font-bold text-gray-900">📊 Dashboard</h1>
                    <p className="text-gray-600">Welcome back! Here's an overview of your procurement activities.</p>
                </div>

                {/* Stats Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div className="flex items-center">
                            <div className="flex-shrink-0">
                                <div className="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <span className="text-blue-600 text-sm">📋</span>
                                </div>
                            </div>
                            <div className="ml-4">
                                <p className="text-sm font-medium text-gray-600">Total Purchase Orders</p>
                                <p className="text-2xl font-bold text-gray-900">{stats.total_purchase_orders}</p>
                            </div>
                        </div>
                    </div>

                    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div className="flex items-center">
                            <div className="flex-shrink-0">
                                <div className="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                                    <span className="text-yellow-600 text-sm">⏳</span>
                                </div>
                            </div>
                            <div className="ml-4">
                                <p className="text-sm font-medium text-gray-600">Pending Validation</p>
                                <p className="text-2xl font-bold text-gray-900">{stats.pending_validation}</p>
                            </div>
                        </div>
                    </div>

                    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div className="flex items-center">
                            <div className="flex-shrink-0">
                                <div className="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <span className="text-blue-600 text-sm">🚀</span>
                                </div>
                            </div>
                            <div className="ml-4">
                                <p className="text-sm font-medium text-gray-600">In Progress</p>
                                <p className="text-2xl font-bold text-gray-900">{stats.in_progress}</p>
                            </div>
                        </div>
                    </div>

                    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div className="flex items-center">
                            <div className="flex-shrink-0">
                                <div className="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                    <span className="text-green-600 text-sm">✅</span>
                                </div>
                            </div>
                            <div className="ml-4">
                                <p className="text-sm font-medium text-gray-600">Completed</p>
                                <p className="text-2xl font-bold text-gray-900">{stats.completed}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Quick Actions */}
                <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 className="text-lg font-semibold text-gray-900 mb-4">🚀 Quick Actions</h2>
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <Link
                            href="/purchase-orders/create"
                            className="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition-colors"
                        >
                            📝 Create Purchase Order
                        </Link>
                        
                        <Link
                            href="/purchase-orders"
                            className="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors"
                        >
                            📋 View All POs
                        </Link>

                        {userPermissions.canCreateCE && (
                            <Link
                                href="/cost-estimates/create"
                                className="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors"
                            >
                                💰 Create Cost Estimate
                            </Link>
                        )}

                        {userPermissions.canManageUsers && (
                            <Link
                                href="/users"
                                className="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors"
                            >
                                👥 Manage Users
                            </Link>
                        )}
                    </div>
                </div>

                {/* Main Content Grid */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Recent Purchase Orders */}
                    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div className="flex items-center justify-between mb-4">
                            <h2 className="text-lg font-semibold text-gray-900">📋 Recent Purchase Orders</h2>
                            <Link
                                href="/purchase-orders"
                                className="text-sm text-blue-600 hover:text-blue-700"
                            >
                                View all
                            </Link>
                        </div>
                        <div className="space-y-4">
                            {recentPurchaseOrders.length > 0 ? (
                                recentPurchaseOrders.map((po) => (
                                    <div key={po.id} className="flex items-center justify-between border-b border-gray-100 pb-3">
                                        <div className="flex-1">
                                            <Link
                                                href={`/purchase-orders/${po.id}`}
                                                className="text-sm font-medium text-gray-900 hover:text-blue-600"
                                            >
                                                {po.po_number}
                                            </Link>
                                            <p className="text-sm text-gray-600 truncate">{po.title}</p>
                                            <p className="text-xs text-gray-500">by {po.created_by.name}</p>
                                        </div>
                                        <div className="flex flex-col items-end space-y-1">
                                            <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusColors[po.status] || 'bg-gray-100 text-gray-800'}`}>
                                                {po.status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                            </span>
                                            <span className={`inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ${priorityColors[po.priority] || 'bg-gray-100 text-gray-800'}`}>
                                                {po.priority.toUpperCase()}
                                            </span>
                                        </div>
                                    </div>
                                ))
                            ) : (
                                <p className="text-gray-500 text-center py-8">No purchase orders yet</p>
                            )}
                        </div>
                    </div>

                    {/* Pending Approvals */}
                    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 className="text-lg font-semibold text-gray-900 mb-4">⏰ Pending Your Approval</h2>
                        <div className="space-y-4">
                            {/* Purchase Orders needing validation */}
                            {pendingApprovals.purchase_orders && pendingApprovals.purchase_orders.length > 0 && (
                                <div>
                                    <h3 className="text-sm font-medium text-gray-700 mb-2">Purchase Orders</h3>
                                    {pendingApprovals.purchase_orders.map((po) => (
                                        <div key={po.id} className="flex items-center justify-between py-2 border-b border-gray-100">
                                            <div>
                                                <Link
                                                    href={`/purchase-orders/${po.id}`}
                                                    className="text-sm font-medium text-gray-900 hover:text-blue-600"
                                                >
                                                    {po.po_number}
                                                </Link>
                                                <p className="text-xs text-gray-500">by {po.created_by.name}</p>
                                            </div>
                                            <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Needs Validation
                                            </span>
                                        </div>
                                    ))}
                                </div>
                            )}

                            {/* Cost Estimates needing approval */}
                            {pendingApprovals.cost_estimates && pendingApprovals.cost_estimates.length > 0 && (
                                <div>
                                    <h3 className="text-sm font-medium text-gray-700 mb-2">Cost Estimates</h3>
                                    {pendingApprovals.cost_estimates.map((ce) => (
                                        <div key={ce.id} className="flex items-center justify-between py-2 border-b border-gray-100">
                                            <div>
                                                <Link
                                                    href={`/cost-estimates/${ce.id}`}
                                                    className="text-sm font-medium text-gray-900 hover:text-blue-600"
                                                >
                                                    {ce.ce_number}
                                                </Link>
                                                <p className="text-xs text-gray-500">PO: {ce.purchase_order.po_number}</p>
                                            </div>
                                            <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Needs Approval
                                            </span>
                                        </div>
                                    ))}
                                </div>
                            )}

                            {(!pendingApprovals.purchase_orders || pendingApprovals.purchase_orders.length === 0) &&
                             (!pendingApprovals.cost_estimates || pendingApprovals.cost_estimates.length === 0) && (
                                <p className="text-gray-500 text-center py-8">No pending approvals</p>
                            )}
                        </div>
                    </div>
                </div>

                {/* Status Overview */}
                <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 className="text-lg font-semibold text-gray-900 mb-4">📈 Status Overview</h2>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {/* Purchase Order Status Distribution */}
                        <div>
                            <h3 className="text-sm font-medium text-gray-700 mb-3">Purchase Order Status</h3>
                            <div className="space-y-2">
                                {Object.entries(charts.purchaseOrderStatuses).map(([status, count]) => (
                                    <div key={status} className="flex items-center justify-between">
                                        <span className="text-sm text-gray-600 capitalize">
                                            {status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                        </span>
                                        <div className="flex items-center">
                                            <div className="w-20 bg-gray-200 rounded-full h-2 mr-2">
                                                <div 
                                                    className="bg-blue-600 h-2 rounded-full" 
                                                    style={{ width: `${Math.max((count / stats.total_purchase_orders) * 100, 5)}%` }}
                                                ></div>
                                            </div>
                                            <span className="text-sm font-medium text-gray-900 w-8 text-right">{count}</span>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* Priority Distribution */}
                        <div>
                            <h3 className="text-sm font-medium text-gray-700 mb-3">Priority Distribution</h3>
                            <div className="space-y-2">
                                {Object.entries(charts.priorityDistribution).map(([priority, count]) => (
                                    <div key={priority} className="flex items-center justify-between">
                                        <span className="text-sm text-gray-600 capitalize">{priority}</span>
                                        <div className="flex items-center">
                                            <div className="w-20 bg-gray-200 rounded-full h-2 mr-2">
                                                <div 
                                                    className={`h-2 rounded-full ${priority === 'urgent' ? 'bg-red-500' : priority === 'high' ? 'bg-orange-500' : priority === 'medium' ? 'bg-yellow-500' : 'bg-green-500'}`}
                                                    style={{ width: `${Math.max((count / stats.total_purchase_orders) * 100, 5)}%` }}
                                                ></div>
                                            </div>
                                            <span className="text-sm font-medium text-gray-900 w-8 text-right">{count}</span>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}