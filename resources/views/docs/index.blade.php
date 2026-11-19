<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentation - RentMate</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            500: '#14b8a6',
                            600: '#0d9488',
                            700: '#0f766e',
                            800: '#115e59',
                            900: '#134e4a',
                        },
                        navy: {
                            800: '#1e293b',
                            900: '#0f172a',
                            950: '#020617',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 font-sans">

    <!-- Top Navigation -->
    <header class="fixed top-0 w-full bg-white/80 backdrop-blur-md border-b border-slate-200 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-20">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-teal-500 to-teal-400 flex items-center justify-center text-white shadow-sm shadow-teal-500/30">
                    <i data-lucide="home" class="w-5 h-5"></i>
                </div>
                <span class="text-lg font-bold tracking-tight text-slate-900">Rent<span class="text-teal-600">Mate</span></span>
            </div>
            <div class="flex items-center gap-3">
                <a href="/" class="text-sm font-medium text-slate-600 hover:text-teal-600 transition">← Back to Home</a>
                <a href="#" class="px-4 py-2 text-sm font-medium text-white bg-teal-600 hover:bg-teal-700 rounded-xl shadow-sm transition-all">Documentation</a>
            </div>
        </div>
    </header>

    <div class="pt-24 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="max-w-2xl mx-auto mb-12 text-center">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold text-teal-700 bg-teal-50 border border-teal-100 mb-6">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-teal-500"></span>
                    </span>
                    Documentation
                </div>
                <h1 class="text-4xl font-bold text-slate-900 mb-4">Complete Guide to RentMate</h2>
                <p class="text-lg text-slate-600 max-w-2xl mx-auto">
                    Everything you need to know about tracking rent, utilities, and payments with RentMate.
                </p>
            </div>

            <!-- Sidebar Navigation -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <aside class="col-span-2 lg:col-span-3 hidden lg:block">
                    <nav class="h-[calc(100vh-150px)] sticky top-24 px-6 pb-20">
                        <div class="flex items-center gap-2 mb-8">
                            <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-teal-500 to-teal-400 flex items-center justify-center text-white shadow-sm shadow-teal-500/30">
                                <i data-lucide="home" class="w-5 h-5"></i>
                            </div>
                            <span class="text-lg font-bold text-slate-900">Rent<span class="text-teal-600">Mate</span></span>
                        </div>
                        <nav class="space-y-2">
                            <a href="#overview" class="block px-4 py-3 rounded-xl bg-teal-50 text-teal-600 font-semibold transition-colors">
                                <span class="w-8 h-8 rounded-lg bg-teal-500/20 text-teal-600 flex items-center justify-center mr-3">
                                    <i data-lucide="home" class="w-4 h-4"></i>
                                </span>
                                Overview
                            </a>
                            <a href="#getting-started" class="block px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors">
                                <span class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center mr-3">
                                    <i data-lucide="rocket" class="w-4 h-4 text-blue-500"></i>
                                </span>
                                Getting Started
                            </a>
                            <a href="#how-it-works" class="block px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors">
                                <span class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center mr-3">
                                    <i data-lucide="zap" class="w-4 h-4 text-blue-500"></i>
                                </span>
                                How It Works
                            </a>
                            <a href="#features" class="block px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors">
                                <span class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center mr-3">
                                    <i data-lucide="layers" class="w-4 h-4 text-slate-500"></i>
                                </span>
                                Feature Breakdown
                            </a>
                            <a href="#data-flow" class="block px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors">
                                <span class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center mr-3">
                                    <i data-lucide="git-pull-request" class="w-4 h-4 text-slate-500"></i>
                                </span>
                                Data Flow &amp; Architecture
                            </a>
                            <a href="#faqs" class="block px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors">
                                <span class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center mr-3">
                                    <i data-lucide="help-circle" class="w-4 h-4 text-slate-500"></i>
                                </span>
                                FAQ
                            </a>
                        </nav>
                    </div>
                </div>

                <!-- Main Content -->
                <main class="col-span-10 lg:col-span-9">
                    <div class="max-w-3xl">
                        <!-- Overview -->
                        <section id="overview" class="mb-16">
                            <h2 class="text-3xl font-bold text-slate-900 mb-4">Overview</h2>
                            <p class="text-lg text-slate-600 mb-6">
                                RentMate is a personal rent &amp; expense tracker built for tenants who want to maintain accurate records of monthly rent, electricity bills, water bills, multi-installment payments, outstanding balances, and historical rental archives.
                            </p>

                            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
                                <h3 class="text-lg font-bold text-slate-900 mb-4">What RentMate Does</h2>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div class="p-6 rounded-xl bg-slate-50 border border-slate-100">
                                        <div class="flex items-center gap-3 mb-4">
                                            <div class="w-10 h-10 rounded-xl bg-teal-100 text-teal-600 flex items-center justify-center">
                                                <i data-lucide="home" class="w-5 h-5"></i>
                                            </div>
                                            <div>
                                                <div class="text-xs font-bold text-slate-700">Monthly Rent</div>
                                                <div class="text-xs text-slate-400">Track monthly rent payments</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3 mb-4">
                                            <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center">
                                                <i data-lucide="zap" class="w-5 h-5"></i>
                                            </div>
                                            <div>
                                                <div class="text-xs font-bold text-slate-700">Electricity</div>
                                                <div class="text-xs text-slate-500">Meter reading tracker</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3 mt-4">
                                            <div class="w-10 h-10 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center">
                                                <i data-lucide="droplets" class="w-5 h-5"></i>
                                            </div>
                                            <div>
                                                <div class="text-xs font-bold text-slate-700">Water Bills</div>
                                                <div class="text-xs text-slate-500">50/50 split tracking</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-12">
                            <h2 class="text-3xl font-bold text-slate-900 mb-4">Core Benefits</h2>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="p-6 rounded-xl bg-white border border-slate-200 shadow-sm">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center mb-4">
                                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                                    </div>
                                    <h3 class="text-lg font-bold text-slate-900 mb-3">Financial Accuracy</h3>
                                    <p class="text-slate-600">All calculations happen server-side with DECIMAL precision. No unreliable frontend calculations.</p>
                                </div>
                                <div class="p-6 rounded-xl bg-white border border-slate-200 shadow-sm">
                                    <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center mb-4">
                                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                                    </div>
                                    <h3 class="text-lg font-bold text-slate-900 mb-3">Data Privacy</h3>
                                    <p class="text-slate-600">Your financial data stays private. Single-tenant application design.</p>
                                </div>
                                <div class="p-6 rounded-xl bg-white border border-slate-200 shadow-sm">
                                    <div class="w-10 h-10 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center mb-4">
                                        <i data-lucide="chart-line" class="w-5 h-5"></i>
                                    </div>
                                    <h3 class="text-lg font-bold text-slate-900 mb-3">Clear Insights</h3>
                                    <p class="text-slate-600">Beautiful charts and reports help you understand your spending patterns.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- How It Works Section -->
                <section id="how-it-works" class="mt-24">
                    <h2 class="text-3xl font-bold text-slate-900 mb-8">How It Works</h2>
                    <p class="text-lg text-slate-600 mb-8 max-w-2xl">
                        RentMate is designed to be simple and straightforward. Here's how you go from signing up to tracking your rental expenses.
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <!-- Step 1 -->
                        <div class="relative group">
                            <div class="w-12 h-12 rounded-full bg-teal-600 text-white flex items-center justify-center mx-auto mb-6 shadow-lg shadow-teal-500/30 relative z-10">
                                <span class="text-lg font-bold">1</span>
                            </div>
                            <div class="text-center">
                                <h3 class="text-xl font-bold text-slate-900 mb-3">Create Your Account</h3>
                                <p class="text-slate-600 leading-relaxed mb-4">Sign up with your email and set up your rental settings — rent amount, electricity rate, water share percentage.</p>
                                <div class="flex justify-center">
                                    <div class="w-10 h-12 rounded-lg border border-slate-200 bg-white/50 flex items-center justify-center text-sm font-bold text-slate-700 border-l-2 border-teal-500">Step 1</div>
                                </div>
                            </div>
                        </div>
                        <!-- Step 2 -->
                        <div class="relative z-10">
                            <div class="w-12 h-12 rounded-full bg-teal-600 text-white flex items-center justify-center mx-auto mb-6 shadow-lg shadow-teal-500/30 relative z-10">
                                <span class="text-lg font-bold">2</span>
                            </div>
                            <div class="text-center">
                                <h3 class="text-xl font-bold text-slate-900 mb-3">Log Your Monthly Records</h3>
                                <p class="text-slate-600 leading-relaxed mb-4">Enter meter readings, water bills, and payments. Calculations happen automatically on the server.</p>
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-10 h-10 rounded-lg bg-white/50 flex items-center justify-center border border-slate-200">
                                            <span class="text-xs font-bold text-slate-800">1</span>
                                        </div>
                                        <div class="w-px h-4 bg-slate-200"></div>
                                        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center border border-slate-200">
                                            <span class="text-xs font-bold text-slate-700">2</span>
                                        </div>
                                        <div class="w-px h-4 bg-slate-200"></div>
                                        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center border border-slate-200">
                                            <span class="text-xs font-bold text-slate-700">3</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Step 3 -->
                        <div class="relative z-10">
                            <div class="w-12 h-12 rounded-full bg-teal-600 text-white flex items-center justify-center mx-auto mb-6 shadow-lg shadow-teal-500/30 relative z-10">
                                <span class="text-lg font-bold">3</span>
                            </div>
                            <div class="text-center">
                                <h3 class="text-xl font-bold text-slate-900 mb-3">Track &amp; Analyze</h3>
                                <p class="text-slate-600 leading-relaxed mb-4">View your dashboard, monitor outstanding balances, and export reports whenever you need them.</p>
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-10 h-10 rounded-lg bg-slate-50 flex items-center justify-center border border-slate-200">
                                            <span class="text-xs font-bold text-slate-700">1</span>
                                        </div>
                                        <div class="w-px h-4 bg-slate-200"></div>
                                        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center border border-slate-200">
                                            <span class="text-xs font-bold text-slate-700">2</span>
                                        </div>
                                        <div class="w-px h-4 bg-slate-200"></div>
                                        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center border border-slate-200">
                                            <span class="text-xs font-bold text-slate-700">3</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- Data Flow Diagram -->
    <section id="data-flow" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-slate-900 mb-4">How Data Flows</h2>
                <p class="text-lg text-slate-600 max-w-2xl mx-auto">Understand how your rental data flows through RentMate from entry to reports.</p>
            </div>

            <!-- Flow Diagram -->
            <div class="relative w-full max-w-4xl mx-auto">
                <!-- Background connections -->
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 h-[400px] w-[100%]">
                    <div class="absolute top-1/4 left-1/4 h-[2px] w-[40%] bg-teal-200"></div>
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 h-[2px] w-[40%] bg-teal-200"></div>
                    <div class="absolute top-3/4 left-1/4 -translate-x-1/2 h-[2px] w-[40%] bg-teal-200"></div>
                    <div class="absolute top-1/4 right-1/4 h-[2px] w-[40%] bg-teal-200"></div>
                    <div class="absolute top-3/4 right-1/4 -translate-x-1/2 h-[2px] w-[40%] bg-teal-200"></div>
                </div>

                <!-- Step 1: Input -->
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
                    <div class="flex flex-col items-center gap-4">
                        <div class="w-16 h-12 rounded-2xl bg-white border border-slate-200 shadow-sm p-3 flex flex-col items-center gap-2">
                            <i data-lucide="user-plus" class="w-6 h-6 text-teal-600 mb-1"></div>
                            <span class="text-xs font-bold text-slate-700">User Input</span>
                            <span class="text-xs text-slate-500">Month, Rent, Readings</span>
                        </div>
                    </div>
                </div>

                <div class="absolute top-1/4 left-1/4 -translate-x-1/2 -translate-y-1/2">
                    <div class="flex flex-col items-center gap-4">
                        <div class="w-16 h-12 rounded-2xl bg-white border border-slate-200 shadow-sm p-3 flex flex-col items-center gap-2">
                            <i data-lucide="zap" class="w-5 h-5 text-amber-500 mb-1"></div>
                            <span class="text-xs font-bold text-slate-700">Meter Readings</span>
                        </div>
                    </div>
                </div>

                <div class="absolute top-3/4 left-1/4 -translate-x-1/2 -translate-y-1/2">
                    <div class="flex flex-col items-center gap-4">
                        <div class="w-16 h-12 rounded-2xl bg-white border border-slate-200 shadow-sm p-3 flex flex-col items-center gap-2">
                            <i data-lucide="droplets" class="w-5 h-5 text-sky-500 mb-1"></div>
                            <span class="text-xs font-bold text-slate-700">Water Bill</span>
                        </div>
                    </div>
                </div>

                <div class="absolute top-1/4 right-1/4 -translate-x-1/2 -translate-y-1/2">
                    <div class="flex flex-col items-center gap-4">
                        <div class="w-16 h-12 rounded-2xl bg-white border border-slate-200 shadow-sm p-3 flex flex-col items-center gap-2">
                            <i data-lucide="wallet" class="w-5 h-5 text-emerald-500 mb-1"></div>
                            <span class="text-xs font-bold text-slate-700">Payments</span>
                        </div>
                    </div>
                </div>

                <div class="absolute top-3/4 right-1/4 -translate-x-1/2 -translate-y-1/2">
                    <div class="flex flex-col items-center gap-4">
                        <div class="w-16 h-12 rounded-2xl bg-white border border-slate-200 shadow-sm p-3 flex flex-col items-center gap-2">
                            <i data-lucide="bar-chart-3" class="w-5 h-5 text-indigo-500 mb-1"></div>
                            <span class="text-xs font-bold text-slate-700">Reports</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-12 text-center">
                <h3 class="text-lg font-bold text-slate-900 mb-4">Data Flow</h2>
                <p class="text-slate-600 mb-6">
                    Data flows from your inputs through calculations to storage and reporting. All financial calculations are performed server-side using DECIMAL precision for accuracy.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <i data-lucide="user-plus" class="w-4 h-4"></i>
                        </div>
                        <span class="text-sm font-semibold text-slate-700">Input</span>
                    </div>
                    <div class="w-8 h-8 rounded-lg bg-blue-500 flex items-center justify-center text-white">
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-white border border-slate-200 flex items-center justify-center">
                            <i data-lucide="calculate" class="w-5 h-5 text-blue-600"></i>
                        </div>
                        <span class="text-sm font-semibold text-slate-700">Calculate</span>
                    </div>
                    <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center">
                        <i data-lucide="database" class="w-4 h-4 text-blue-600"></i>
                    </div>
                    <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center">
                        <i data-lucide="file-text" class="w-5 h-5 text-blue-600"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Core Features Section -->
    <section id="features" class="py-24 bg-white border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-slate-900 mb-4">Core Features</h2>
                <p class="text-lg text-slate-600 max-w-2xl mx-auto">Everything you need to track your rental expenses.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="p-6 rounded-2xl border border-slate-200 bg-white shadow-sm hover:border-teal-200 hover:bg-teal-50/50 transition-colors">
                    <div class="w-12 h-12 rounded-xl bg-teal-100 text-teal-600 flex items-center justify-center mb-6 group-hover:bg-teal-600 group-hover:text-white transition-colors">
                        <i data-lucide="calendar-days" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-3">Monthly Records</h3>
                    <p class="text-slate-600 leading-relaxed">Create, view, edit, and delete monthly rental records. Unique constraints prevent duplicate entries for the same month.</p>
                </div>

                <div class="p-6 rounded-xl bg-white border border-slate-200 shadow-sm">
                    <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center mb-6 group-hover:bg-amber-600 group-hover:text-white transition-colors">
                        <i data-lucide="zap" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-3">Electricity Tracker</h3>
                    <p class="text-slate-600 leading-relaxed">Enter meter readings — units consumed and cost are calculated automatically. Current reading can't be lower than previous.</p>
                </div>

                <div class="p-6 rounded-xl bg-white border border-slate-200 shadow-sm">
                    <div class="w-12 h-12 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center mb-6 group-hover:bg-sky-600 group-hover:text-white transition-colors">
                        <i data-lucide="droplets" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-3">Water Bill Split</h3>
                    <p class="text-slate-600 leading-relaxed">Configurable tenant/owner split (default 50/50). Tenant share and owner share are tracked automatically.</p>
                </div>

                <div class="p-6 rounded-xl bg-white border border-slate-200 shadow-sm">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center mb-6 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                        <i data-lucide="wallet" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-3">Multi-Installment Payments</h3>
                    <p class="text-slate-600 leading-relaxed">Pay rent in multiple installments (Cash, eSewa, Khalti, Bank Transfer). Status auto-updates: Unpaid → Partially → Paid → Overpaid.</p>
                </div>

                <div class="p-6 rounded-xl bg-white border border-slate-200 shadow-sm">
                    <div class="w-12 h-12 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center mb-6 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                        <i data-lucide="history" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-3">Historical Archives</h3>
                    <p class="text-slate-600 leading-relaxed">Log older months from previous tenancy. Mark records as Verified or Estimated — missing payments are never invented.</p>
                </div>

                <div class="p-6 rounded-xl bg-white border border-slate-200 shadow-sm">
                    <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center mb-6 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                        <i data-lucide="bar-chart-3" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-3">Reports & Exports</h3>
                    <p class="text-slate-600 leading-relaxed">Filter by year or date range. Export to CSV/Excel with UTF-8 BOM or download printable PDF statements.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faqs" class="py-24 bg-slate-50 border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl mx-auto mb-16 text-center">
                <h2 class="text-3xl font-bold text-slate-900 mb-4">Frequently Asked Questions</h2>
                <p class="text-lg text-slate-600">Common questions about RentMate.</p>
            </div>

            <div class="space-y-3">
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
                    <button class="w-full flex items-center justify-between p-6 text-left group" onclick="toggleFaq(1)">
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-bold text-slate-900">How do I add a monthly record?</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transform rotate-0 transition-transform duration-300"></i>
                        </div>
                        <div class="max-h-0 overflow-hidden transition-all duration-300 pt-4 text-slate-600 text-sm leading-relaxed">
                            Enter your monthly rent, electricity meter readings, water bill, and any payments you made. The system will automatically calculate totals, balances, and status.
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-white border border-slate-200 flex items-center justify-center shrink-0">
                            <i data-lucide="check-circle" class="w-4 h-4 text-teal-600"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-900">Done</span>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
                    <div class="flex items-center justify-between p-6">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-900">How are electricity costs calculated?</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 shrink-0 transform rotate-0 transition-transform duration-300"></i>
                        </div>
                    </div>
                    <div class="max-h-0 overflow-hidden transition-all duration-300 text-xs text-slate-600">
                        Electricity cost is calculated as: Units Consumed × Electricity Rate per Unit. Units Consumed = Current Meter Reading - Previous Meter Reading.
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-white border border-slate-200 flex items-center justify-center shrink-0">
                        <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transform rotate-0 transition-transform duration-300"></i>
                    </div>
                    <span class="text-xs font-bold text-slate-900">How is water bill shared?</span>
                </div>
                <div class="max-h-0 overflow-hidden transition-all duration-300 text-xs text-slate-600">
                    Tenant share = Total Water Bill × Tenant Share Percentage ÷ 100. Owner share = Total Water Bill - Tenant Share.
                </div>
                </div>
            </div>
        </div>
    </section>

    </div>
</div>

    <footer class="bg-white border-t border-slate-200 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-6 h-8 rounded-xl bg-gradient-to-tr from-teal-500 to-teal-400 flex items-center justify-center text-white shadow-sm shadow-teal-500/30">
                    <i data-lucide="home" class="w-3 h-3"></i>
                </div>
                <span class="font-bold text-slate-900">Rent<span class="text-teal-600">Mate</span></span>
            </div>
            <p class="text-xs text-slate-500">RentMate - Personal Rent & Expense Tracker</p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.lucide) window.lucide.createIcons();
        });
    </script>
    @stack('scripts')
</body>
</html>
