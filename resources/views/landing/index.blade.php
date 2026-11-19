<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentMate – Personal Rent & Expense Tracker</title>
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
        .gradient-text {
            background: linear-gradient(135deg, #0d9488 0%, #0f172a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900">

    <!-- Navigation -->
    <nav class="fixed w-full z-50 bg-white/80 backdrop-blur-md border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-teal-500 to-teal-400 flex items-center justify-center text-white shadow-sm shadow-teal-500/30">
                        <i data-lucide="home" class="w-5 h-5"></i>
                    </div>
                    <span class="text-lg font-bold tracking-tight text-slate-900">Rent<span class="text-teal-600">Mate</span></span>
                </div>
                <div class="hidden md:flex items-center gap-6">
                    <a href="#features" class="text-sm font-medium text-slate-600 hover:text-teal-600 transition">Features</a>
                    <a href="#how-it-works" class="text-xs font-medium text-slate-600 hover:text-teal-600 transition">How it works</a>
                    <a href="#pricing" class="text-sm font-medium text-slate-600 hover:text-teal-600 transition">Pricing</a>
                    <a href="{{ route('docs.index') }}" class="text-xs font-medium text-slate-600 hover:text-teal-600 transition">Docs</a>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('login') }}" class="text-xs font-medium text-slate-600 hover:text-teal-600 transition">Sign in</a>
                    <a href="{{ route('register') }}" class="px-4 py-2 text-sm font-semibold text-white bg-teal-600 hover:bg-teal-700 rounded-xl shadow-sm shadow-teal-900/20 transition-all">Get Started</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-3xl mx-auto">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold text-teal-700 bg-teal-50 border border-teal-100 mb-6">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-teal-500"></span>
                    </span>
                    Built for tenants in Nepal
                </div>
                <h1 class="text-5xl lg:text-7xl font-bold tracking-tight leading-tight mb-6 gradient-text">
                    Take control of your<br>
                    <span class="text-teal-600">rental expenses</span>
                </h1>
                <p class="text-lg lg:text-xl text-slate-600 mb-8 max-w-xl mx-auto leading-relaxed">
                    Track monthly rent, electricity (via meter readings), water bills (50/50 split), multi-installment payments,
                    outstanding balances, and historical records — all with accurate server-side calculations.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-4 text-lg font-bold text-white bg-teal-600 hover:bg-teal-700 rounded-2xl shadow-lg shadow-teal-900/20 transition-all flex items-center justify-center gap-2">
                        <i data-lucide="user-plus" class="w-5 h-5"></i>
                        Start tracking free
                    </a>
                    <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-4 text-lg font-bold text-slate-700 bg-white border border-slate-200 hover:border-teal-600 hover:text-teal-600 rounded-2xl shadow-sm transition-all flex items-center justify-center gap-2">
                        <i data-lucide="log-in" class="w-5 h-5"></i>
                        Sign in
                    </a>
                </div>
            </div>

            <!-- Hero Visual -->
            <div class="mt-16 relative">
                <div class="absolute -inset-1 bg-gradient-to-r from-teal-400 to-teal-600 rounded-3xl blur opacity-20"></div>
                <div class="relative bg-white rounded-3xl border border-slate-200 shadow-2xl p-8">
                    <div class="flex flex-col lg:flex-row gap-8 items-start lg:items-center">
                        <div class="lg:w-1/2 space-y-6">
                            <div class="bg-slate-50/50 p-4 rounded-xl border border-slate-100">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-8 h-8 rounded-lg bg-teal-100 text-teal-600 flex items-center justify-center">
                                        <i data-lucide="home" class="w-5 h-5"></i>
                                    </div>
                                    <span class="text-sm font-bold text-slate-800">Monthly Rent Tracking</div>
                                </div>
                                <p class="text-xs text-slate-500">Complete rental history at a glance</p>
                            </div>
                            <div class="bg-slate-50/50 p-4 rounded-xl border border-slate-100">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
                                        <i data-lucide="zap" class="w-5 h-5"></i>
                                    </div>
                                    <span class="text-sm font-bold text-slate-800">Electricity Tracker</span>
                                </div>
                                <p class="text-xs text-slate-500">Meter readings → units → cost</p>
                            </div>
                            <div class="bg-slate-50/50 p-4 rounded-xl border border-slate-100">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-8 h-8 rounded-lg bg-sky-100 text-sky-600 flex items-center justify-center">
                                        <i data-lucide="droplets" class="w-5 h-5"></i>
                                    </div>
                                    <span class="text-sm font-bold text-slate-800">50/50 Water Split</span>
                                </div>
                                <p class="text-xs text-slate-500">Tenant & owner shares tracked</p>
                            </div>
                        </div>
                        <div class="lg:w-1/2">
                            <div class="relative">
                                <div class="absolute -inset-0.5 bg-gradient-to-r from-teal-400 to-teal-600 rounded-3xl blur opacity-30"></div>
                                <div class="bg-slate-900 rounded-2xl p-6 shadow-2xl overflow-hidden">
                                    <div class="flex items-center justify-between mb-4">
                                        <span class="flex items-center gap-1 text-teal-400 text-xs font-bold">
                                            <i data-lucide="clock" class="w-3 h-3"></i> Live
                                        </span>
                                        <span class="text-xs text-slate-400">NPR 16,000.00</span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-xs text-slate-400 mb-1">Rent Due</p>
                                            <p class="text-lg font-bold text-slate-100">NPR 0.00</p>
                                            <span class="text-[10px] text-teal-400">Paid on time</span>
                                        </div>
                                        <div>
                                            <p class="text-xs text-slate-400 mb-1">Balance</p>
                                            <p class="text-lg font-bold text-emerald-400">NPR 0.00</p>
                                            <span class="text-[10px] text-emerald-400">All clear</span>
                                        </div>
                                    </div>
                                    <div class="mt-6">
                                        <div class="flex justify-between text-xs mb-1">
                                            <span class="text-slate-400">Electricity</span>
                                            <span class="text-slate-200">NPR 850.00</span>
                                        </div>
                                        <div class="w-full bg-slate-800 rounded-full h-1.5">
                                            <div class="bg-teal-500 h-1.5 rounded-full" style="width: 65%"></div>
                                        </div>
                                        <div class="flex justify-between text-xs mt-1">
                                            <span class="text-slate-400">Water (50%)</span>
                                            <span class="text-slate-200">NPR 430.00</span>
                                        </div>
                                        <div class="w-full bg-slate-800 rounded-full h-1.5">
                                            <div class="bg-sky-500 h-1.5 rounded-full" style="width: 50%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-24 bg-white border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl lg:text-4xl font-bold tracking-tight text-slate-900 mb-4">Everything you need</h2>
                <p class="text-lg text-slate-600 max-w-2xl mx-auto">From your first rent payment to your last utility bill, RentMate handles it all.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="p-8 rounded-2xl border border-slate-200 bg-slate-50 hover:border-teal-200 hover:bg-teal-50/50 transition-all group">
                    <div class="w-10 h-12 rounded-xl bg-teal-100 text-teal-600 flex items-center justify-center mb-6 group-hover:bg-teal-600 group-hover:text-white transition-colors">
                        <i data-lucide="calendar-days" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Monthly Records</h3>
                    <p class="text-slate-600 leading-relaxed">Create, view, edit, and delete monthly rental records. Unique constraints prevent duplicate entries for the same month.</p>
                </div>

                <div class="p-8 rounded-2xl border border-slate-200 bg-slate-50 hover:border-teal-200 hover:bg-teal-50/50 transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center mb-6 group-hover:bg-amber-600 group-hover:text-white transition-colors">
                        <i data-lucide="zap" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Electricity Tracker</h3>
                    <p class="text-slate-600 leading-relaxed">Enter meter readings — units consumed and cost are calculated automatically. Current reading can't be lower than previous.</p>
                </div>

                <div class="p-8 rounded-2xl border border-slate-200 bg-slate-50 hover:border-teal-200 hover:bg-teal-50/50 transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center mb-6 group-hover:bg-sky-600 group-hover:text-white transition-colors">
                        <i data-lucide="droplets" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Water Bill Split</h3>
                    <p class="text-slate-600 leading-relaxed">Configurable tenant/owner split (default 50/50). Tenant share and owner share are tracked automatically.</p>
                </div>

                <div class="p-8 rounded-2xl border border-slate-200 bg-slate-50 hover:border-teal-200 hover:bg-teal-50/50 transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center mb-6 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                        <i data-lucide="wallet" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Multi-Installment Payments</h3>
                    <p class="text-slate-600 leading-relaxed">Pay rent in multiple installments (Cash, eSewa, Khalti, Bank Transfer). Status auto-updates: Unpaid → Partially → Paid → Overpaid.</p>
                </div>

                <div class="p-8 rounded-2xl border border-slate-200 bg-slate-50 hover:border-teal-200 hover:bg-teal-50/50 transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center mb-6 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                        <i data-lucide="history" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Historical Archives</h3>
                    <p class="text-slate-600 leading-relaxed">Log older months from previous tenancy. Mark records as Verified or Estimated — missing payments are never invented.</p>
                </div>

                <div class="p-8 rounded-2xl border border-slate-200 bg-slate-50 hover:border-teal-200 hover:bg-teal-50/50 transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center mb-6 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                        <i data-lucide="bar-chart-3" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-3">Reports & Exports</h3>
                    <p class="text-slate-600 leading-relaxed">Filter by year or date range. Export to CSV/Excel with UTF-8 BOM or download printable PDF statements.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-24 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl lg:text-4xl font-bold tracking-tight text-slate-900 mb-4">Simple as 1-2-3</h2>
                <p class="text-lg text-slate-600 max-w-2xl mx-auto">Get your rental tracking started in minutes.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8 relative">
                <!-- Step 1 -->
                <div class="relative">
                    <div class="w-12 h-12 rounded-full bg-teal-600 text-white flex items-center justify-center mx-auto mb-6 shadow-lg shadow-teal-500/30 relative z-10">
                        <span class="text-lg font-bold">1</span>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-slate-900 mb-3">Create Your Account</h3>
                        <p class="text-slate-600 leading-relaxed">Sign up with your email and set up your rental settings — rent amount, electricity rate, water split percentage.</p>
                    </div>
                </div>
                <!-- Step 2 -->
                <div class="relative">
                    <div class="w-12 h-12 rounded-full bg-teal-600 text-white flex items-center justify-center mx-auto mb-6 shadow-lg shadow-teal-500/30 relative z-10">
                        <span class="text-lg font-bold">2</span>
                    </div>
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-slate-900 mb-3">Log Your Monthly Records</h3>
                        <p class="text-slate-600 leading-relaxed mb-4">Enter meter readings, water bills, and payments. Calculations happen automatically on the server.</p>
                        <div class="flex flex-col items-center justify-center gap-2">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center border border-slate-200">
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
                        <div class="flex items-center justify-center">
                            <div class="w-10 h-12 rounded-lg bg-slate-50 flex items-center justify-center border border-slate-200 text-sm font-bold text-slate-700 border-l-2 border-teal-500">3</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section id="pricing" class="py-24 border-t border-slate-200">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-gradient-to-br from-teal-600 to-navy-900 rounded-3xl p-8 sm:p-12 text-white shadow-2xl shadow-teal-900/30 relative overflow-hidden">
                <div class="absolute top-0 -right-10 w-32 h-32 bg-teal-500/20 rounded-full blur-3xl"></div>
                <div class="relative z-10 text-center">
                    <h2 class="text-3xl lg:text-4xl font-bold mb-4">Ready to track your rent?</h2>
                    <p class="text-teal-100 mb-8 text-lg">Join hundreds of tenants keeping accurate rental records.</p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="{{ route('register') }}" class="px-8 py-4 text-lg font-bold text-teal-600 bg-white rounded-2xl shadow-xl hover:shadow-teal-500/30 transition-all flex items-center justify-center gap-2">
                            <i data-lucide="user-plus" class="w-5 h-5"></i>
                            Create Account
                        </a>
                        <a href="{{ route('login') }}" class="px-8 py-4 text-lg font-bold text-white bg-white/10 border border-white/20 rounded-2xl hover:bg-white/20 transition-all flex items-center justify-center gap-2">
                            <i data-lucide="log-in" class="w-5 h-5"></i>
                            Sign In
                        </a>
                    </div>
                    <p class="mt-8 text-xs text-teal-200 uppercase tracking-wider">No credit card required · Cancel anytime</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-slate-200 bg-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg bg-gradient-to-tr from-teal-500 to-teal-400 flex items-center justify-center text-white">
                        <i data-lucide="home" class="w-3 h-3"></i>
                    </div>
                    <span class="font-bold text-slate-900">Rent<span class="text-teal-600">Mate</span></span>
                </div>
                <p class="text-sm text-slate-500">
                    <a href="#" class="text-teal-600 hover:text-teal-700">Privacy</a> ·
                    <a href="#" class="text-teal-600 hover:text-teal-700">Terms</a> ·
                    <a href="#" class="text-teal-600 hover:text-teal-700">Contact</a>
                </p>
                <div class="flex items-center gap-4 text-sm text-slate-400">
                    <a href="#" class="hover:text-teal-600 transition"><i data-lucide="github" class="w-4 h-4"></i></a>
                    <a href="#" class="hover:text-teal-600 transition"><i data-lucide="twitter" class="w-4 h-4"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
