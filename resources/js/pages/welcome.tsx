import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';

export default function Welcome() {
    const { auth } = usePage<SharedData>().props;

    return (
        <>
            <Head title="PO Management System">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="flex min-h-screen flex-col items-center bg-gradient-to-br from-blue-50 via-white to-indigo-50 p-6 text-gray-900">
                <header className="mb-8 w-full max-w-6xl">
                    <nav className="flex items-center justify-between">
                        <div className="flex items-center space-x-2">
                            <div className="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                                <span className="text-white font-bold text-sm">📋</span>
                            </div>
                            <span className="font-semibold text-lg">PO Management</span>
                        </div>
                        <div className="flex items-center gap-4">
                            {auth.user ? (
                                <Link
                                    href={route('dashboard')}
                                    className="inline-flex items-center justify-center px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
                                >
                                    Go to Dashboard
                                </Link>
                            ) : (
                                <>
                                    <Link
                                        href={route('login')}
                                        className="inline-flex items-center justify-center px-4 py-2 text-gray-700 hover:text-blue-600 transition-colors"
                                    >
                                        Log in
                                    </Link>
                                    <Link
                                        href={route('register')}
                                        className="inline-flex items-center justify-center px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
                                    >
                                        Get Started
                                    </Link>
                                </>
                            )}
                        </div>
                    </nav>
                </header>

                <main className="w-full max-w-6xl">
                    {/* Hero Section */}
                    <div className="text-center mb-16">
                        <div className="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-6">
                            <span className="text-2xl">📊</span>
                        </div>
                        <h1 className="text-4xl md:text-5xl font-bold mb-6 bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                            Purchase Order Management System
                        </h1>
                        <p className="text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
                            Streamline your procurement process with robust role-based access control, 
                            multi-step approval workflows, and comprehensive cost estimation tools.
                        </p>
                        {!auth.user && (
                            <div className="flex items-center justify-center gap-4">
                                <Link
                                    href={route('register')}
                                    className="inline-flex items-center justify-center px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-lg"
                                >
                                    Start Managing POs
                                </Link>
                                <Link
                                    href={route('login')}
                                    className="inline-flex items-center justify-center px-8 py-3 border border-gray-300 text-gray-700 rounded-lg hover:border-gray-400 transition-colors font-medium text-lg"
                                >
                                    Sign In
                                </Link>
                            </div>
                        )}
                    </div>

                    {/* Features Grid */}
                    <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
                        <div className="bg-white rounded-xl p-6 shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                            <div className="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                                <span className="text-xl">✅</span>
                            </div>
                            <h3 className="text-lg font-semibold mb-2">Multi-Step Approval</h3>
                            <p className="text-gray-600">
                                Structured workflow with validation, cost estimation, and final approval stages.
                            </p>
                        </div>

                        <div className="bg-white rounded-xl p-6 shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                            <div className="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                                <span className="text-xl">👥</span>
                            </div>
                            <h3 className="text-lg font-semibold mb-2">Role-Based Access</h3>
                            <p className="text-gray-600">
                                Granular permissions for Superadmin, Admin, Unit Kerja, BSP, KKF, and DAU roles.
                            </p>
                        </div>

                        <div className="bg-white rounded-xl p-6 shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                            <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                                <span className="text-xl">💰</span>
                            </div>
                            <h3 className="text-lg font-semibold mb-2">Cost Estimation</h3>
                            <p className="text-gray-600">
                                Detailed Cost Estimates and Bill of Quantities with item-level breakdowns.
                            </p>
                        </div>

                        <div className="bg-white rounded-xl p-6 shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                            <div className="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mb-4">
                                <span className="text-xl">📈</span>
                            </div>
                            <h3 className="text-lg font-semibold mb-2">Progress Tracking</h3>
                            <p className="text-gray-600">
                                Real-time status updates from draft to completion with audit trails.
                            </p>
                        </div>

                        <div className="bg-white rounded-xl p-6 shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                            <div className="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mb-4">
                                <span className="text-xl">🎯</span>
                            </div>
                            <h3 className="text-lg font-semibold mb-2">Priority Management</h3>
                            <p className="text-gray-600">
                                Organize purchase orders by priority levels: Low, Medium, High, and Urgent.
                            </p>
                        </div>

                        <div className="bg-white rounded-xl p-6 shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                            <div className="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                                <span className="text-xl">📊</span>
                            </div>
                            <h3 className="text-lg font-semibold mb-2">Analytics Dashboard</h3>
                            <p className="text-gray-600">
                                Comprehensive insights with charts and statistics for better decision making.
                            </p>
                        </div>
                    </div>

                    {/* Workflow Section */}
                    <div className="bg-white rounded-2xl p-8 shadow-sm border border-gray-200 mb-16">
                        <h2 className="text-2xl font-bold text-center mb-8">Streamlined Workflow</h2>
                        <div className="grid md:grid-cols-4 gap-6">
                            <div className="text-center">
                                <div className="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <span className="text-2xl">📝</span>
                                </div>
                                <h3 className="font-semibold mb-2">1. Create PO</h3>
                                <p className="text-sm text-gray-600">Users create purchase orders with detailed specifications</p>
                            </div>

                            <div className="text-center">
                                <div className="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <span className="text-2xl">✅</span>
                                </div>
                                <h3 className="font-semibold mb-2">2. Validation</h3>
                                <p className="text-sm text-gray-600">BSP validates and approves purchase orders</p>
                            </div>

                            <div className="text-center">
                                <div className="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <span className="text-2xl">💰</span>
                                </div>
                                <h3 className="font-semibold mb-2">3. Cost Estimate</h3>
                                <p className="text-sm text-gray-600">BSP creates detailed cost estimates or BOQ</p>
                            </div>

                            <div className="text-center">
                                <div className="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <span className="text-2xl">🚀</span>
                                </div>
                                <h3 className="font-semibold mb-2">4. Final Approval</h3>
                                <p className="text-sm text-gray-600">DAU approves cost estimates, PO moves to progress</p>
                            </div>
                        </div>
                    </div>

                    {/* CTA Section */}
                    {!auth.user && (
                        <div className="text-center bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-8 text-white">
                            <h2 className="text-2xl font-bold mb-4">Ready to Transform Your Procurement Process?</h2>
                            <p className="text-blue-100 mb-6 max-w-2xl mx-auto">
                                Join organizations that trust our platform to manage their purchase orders efficiently 
                                with complete transparency and control.
                            </p>
                            <Link
                                href={route('register')}
                                className="inline-flex items-center justify-center px-8 py-3 bg-white text-blue-600 rounded-lg hover:bg-gray-50 transition-colors font-medium text-lg"
                            >
                                Get Started Today
                            </Link>
                        </div>
                    )}
                </main>

                <footer className="mt-16 text-center text-gray-500">
                    <p className="text-sm">
                        Built with ❤️ by{' '}
                        <a 
                            href="https://app.build" 
                            target="_blank" 
                            className="font-medium text-blue-600 hover:underline"
                        >
                            app.build
                        </a>
                    </p>
                </footer>
            </div>
        </>
    );
}