import { Head, Link } from '@inertiajs/react';
import { AppLayout } from '@/components/app-layout';
import { type BreadcrumbItem, type SharedData } from '@/types';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
}

interface PurchaseOrder {
    id: number;
    po_number: string;
    title: string;
    description?: string;
    estimated_value?: number;
    status: string;
    priority: string;
    required_by?: string;
    created_by: User;
    validated_by?: User;
    completed_by?: User;
    validation_notes?: string;
    completion_notes?: string;
    created_at: string;
    updated_at: string;
}

interface Props extends SharedData {
    purchaseOrder: PurchaseOrder;
    can: {
        edit: boolean;
        delete: boolean;
        validate: boolean;
        complete: boolean;
        createCostEstimate: boolean;
    };
    [key: string]: unknown;
}

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
};

const priorityColors: Record<string, string> = {
    low: 'bg-green-100 text-green-800',
    medium: 'bg-yellow-100 text-yellow-800',
    high: 'bg-orange-100 text-orange-800',
    urgent: 'bg-red-100 text-red-800',
};

export default function PurchaseOrderShow({ purchaseOrder, can }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Purchase Orders', href: '/purchase-orders' },
        { title: purchaseOrder.po_number, href: `/purchase-orders/${purchaseOrder.id}` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Purchase Order ${purchaseOrder.po_number}`} />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">📋 {purchaseOrder.po_number}</h1>
                        <p className="text-gray-600">{purchaseOrder.title}</p>
                    </div>
                    <div className="flex items-center space-x-3">
                        {can.edit && (
                            <Link
                                href={`/purchase-orders/${purchaseOrder.id}/edit`}
                                className="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors"
                            >
                                ✏️ Edit
                            </Link>
                        )}
                        {can.createCostEstimate && (
                            <Link
                                href={`/cost-estimates/create?purchase_order_id=${purchaseOrder.id}`}
                                className="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 transition-colors"
                            >
                                💰 Create Cost Estimate
                            </Link>
                        )}
                    </div>
                </div>

                {/* Purchase Order Details */}
                <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 className="text-lg font-semibold text-gray-900 mb-4">📄 Purchase Order Details</h3>
                            <div className="space-y-3">
                                <div>
                                    <label className="text-sm font-medium text-gray-500">PO Number</label>
                                    <p className="text-sm text-gray-900">{purchaseOrder.po_number}</p>
                                </div>
                                <div>
                                    <label className="text-sm font-medium text-gray-500">Title</label>
                                    <p className="text-sm text-gray-900">{purchaseOrder.title}</p>
                                </div>
                                {purchaseOrder.description && (
                                    <div>
                                        <label className="text-sm font-medium text-gray-500">Description</label>
                                        <p className="text-sm text-gray-900">{purchaseOrder.description}</p>
                                    </div>
                                )}
                                {purchaseOrder.estimated_value && (
                                    <div>
                                        <label className="text-sm font-medium text-gray-500">Estimated Value</label>
                                        <p className="text-sm text-gray-900">${purchaseOrder.estimated_value.toLocaleString()}</p>
                                    </div>
                                )}
                                {purchaseOrder.required_by && (
                                    <div>
                                        <label className="text-sm font-medium text-gray-500">Required By</label>
                                        <p className="text-sm text-gray-900">{new Date(purchaseOrder.required_by).toLocaleDateString()}</p>
                                    </div>
                                )}
                            </div>
                        </div>

                        <div>
                            <h3 className="text-lg font-semibold text-gray-900 mb-4">📊 Status & Priority</h3>
                            <div className="space-y-3">
                                <div>
                                    <label className="text-sm font-medium text-gray-500">Status</label>
                                    <div className="mt-1">
                                        <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusColors[purchaseOrder.status] || 'bg-gray-100 text-gray-800'}`}>
                                            {purchaseOrder.status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <label className="text-sm font-medium text-gray-500">Priority</label>
                                    <div className="mt-1">
                                        <span className={`inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ${priorityColors[purchaseOrder.priority] || 'bg-gray-100 text-gray-800'}`}>
                                            {purchaseOrder.priority.toUpperCase()}
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <label className="text-sm font-medium text-gray-500">Created By</label>
                                    <p className="text-sm text-gray-900">{purchaseOrder.created_by.name}</p>
                                    <p className="text-xs text-gray-500">{purchaseOrder.created_by.role.replace(/_/g, ' ').toUpperCase()}</p>
                                </div>
                                <div>
                                    <label className="text-sm font-medium text-gray-500">Created At</label>
                                    <p className="text-sm text-gray-900">{new Date(purchaseOrder.created_at).toLocaleDateString()}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Workflow Information */}
                {(purchaseOrder.validated_by || purchaseOrder.completed_by) && (
                    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 className="text-lg font-semibold text-gray-900 mb-4">🔄 Workflow History</h3>
                        <div className="space-y-4">
                            {purchaseOrder.validated_by && (
                                <div className="border-l-4 border-blue-500 pl-4">
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <h4 className="text-sm font-medium text-gray-900">Validated</h4>
                                            <p className="text-sm text-gray-600">by {purchaseOrder.validated_by.name}</p>
                                        </div>
                                        <div className="text-right">
                                            <p className="text-sm text-gray-500">Validation Date</p>
                                            <p className="text-sm text-gray-900">
                                                {purchaseOrder.validation_notes ? 'With notes' : 'No notes'}
                                            </p>
                                        </div>
                                    </div>
                                    {purchaseOrder.validation_notes && (
                                        <div className="mt-2">
                                            <p className="text-sm text-gray-700 bg-gray-50 p-2 rounded">
                                                {purchaseOrder.validation_notes}
                                            </p>
                                        </div>
                                    )}
                                </div>
                            )}

                            {purchaseOrder.completed_by && (
                                <div className="border-l-4 border-green-500 pl-4">
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <h4 className="text-sm font-medium text-gray-900">Completed</h4>
                                            <p className="text-sm text-gray-600">by {purchaseOrder.completed_by.name}</p>
                                        </div>
                                        <div className="text-right">
                                            <p className="text-sm text-gray-500">Completion Date</p>
                                            <p className="text-sm text-gray-900">
                                                {purchaseOrder.completion_notes ? 'With notes' : 'No notes'}
                                            </p>
                                        </div>
                                    </div>
                                    {purchaseOrder.completion_notes && (
                                        <div className="mt-2">
                                            <p className="text-sm text-gray-700 bg-gray-50 p-2 rounded">
                                                {purchaseOrder.completion_notes}
                                            </p>
                                        </div>
                                    )}
                                </div>
                            )}
                        </div>
                    </div>
                )}

                {/* Actions */}
                <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 className="text-lg font-semibold text-gray-900 mb-4">⚡ Available Actions</h3>
                    <div className="flex flex-wrap gap-3">
                        <Link
                            href="/purchase-orders"
                            className="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 transition-colors"
                        >
                            ← Back to Purchase Orders
                        </Link>
                        {can.edit && (
                            <Link
                                href={`/purchase-orders/${purchaseOrder.id}/edit`}
                                className="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors"
                            >
                                ✏️ Edit Purchase Order
                            </Link>
                        )}
                        {can.createCostEstimate && (
                            <Link
                                href={`/cost-estimates/create?purchase_order_id=${purchaseOrder.id}`}
                                className="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 transition-colors"
                            >
                                💰 Create Cost Estimate
                            </Link>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}