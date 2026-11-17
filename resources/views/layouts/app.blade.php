<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - RentMate</title>
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
    <script src="{{ asset('js/lucide.min.js') }}"></script>
    <!-- Chart.js -->
    <script src="{{ asset('js/chart.min.js') }}"></script>
    <style>
        [x-cloak] { display: none !important; }
        @media print {
            .no-print { display: none !important; }
            .print-only { display: block !important; }
        }
    </style>
    @stack('styles')
</head>
<body class="h-full text-slate-800 antialiased font-sans">
    <div class="min-h-full flex flex-col md:flex-row">
        <!-- Mobile Sidebar Backdrop -->
        <div id="mobile-sidebar-backdrop" class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm hidden md:hidden" onclick="toggleSidebar()"></div>

        <!-- Sidebar Navigation -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-navy-900 text-slate-300 transform -translate-x-full md:translate-x-0 md:static md:inset-auto md:flex md:flex-col transition-transform duration-300 ease-in-out shrink-0">
            <!-- Brand Logo -->
            <div class="h-16 flex items-center justify-between px-6 border-b border-slate-800/80 bg-navy-950/40">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-teal-500 to-teal-400 flex items-center justify-center text-white shadow-md shadow-teal-500/20 group-hover:scale-105 transition-transform">
                        <i data-lucide="home" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="text-lg font-bold tracking-tight text-white flex items-center gap-1">Rent<span class="text-teal-400">Mate</span></span>
                        <span class="text-[10px] text-slate-400 uppercase tracking-widest block font-medium">Expense Tracker</span>
                    </div>
                </a>
                <button class="md:hidden text-slate-400 hover:text-white" onclick="toggleSidebar()">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-3 py-5 space-y-1 overflow-y-auto">
                <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('dashboard') ? 'bg-teal-600/15 text-teal-400 border border-teal-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                    <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3 {{ request()->routeIs('dashboard') ? 'text-teal-400' : 'text-slate-400' }}"></i>
                    Dashboard
                </a>

                <a href="{{ route('monthly-records.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('monthly-records.*') ? 'bg-teal-600/15 text-teal-400 border border-teal-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                    <i data-lucide="calendar-days" class="w-5 h-5 mr-3 {{ request()->routeIs('monthly-records.*') ? 'text-teal-400' : 'text-slate-400' }}"></i>
                    Monthly Records
                </a>

                <a href="{{ route('electricity.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('electricity.*') ? 'bg-teal-600/15 text-teal-400 border border-teal-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                    <i data-lucide="zap" class="w-5 h-5 mr-3 {{ request()->routeIs('electricity.*') ? 'text-teal-400' : 'text-slate-400' }}"></i>
                    Electricity Tracker
                </a>

                <a href="{{ route('water.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('water.*') ? 'bg-teal-600/15 text-teal-400 border border-teal-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                    <i data-lucide="droplets" class="w-5 h-5 mr-3 {{ request()->routeIs('water.*') ? 'text-teal-400' : 'text-slate-400' }}"></i>
                    Water Bills (50/50)
                </a>

                <a href="{{ route('payments.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('payments.*') ? 'bg-teal-600/15 text-teal-400 border border-teal-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                    <i data-lucide="wallet" class="w-5 h-5 mr-3 {{ request()->routeIs('payments.*') ? 'text-teal-400' : 'text-slate-400' }}"></i>
                    Payments
                </a>

                <a href="{{ route('historical.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('historical.*') ? 'bg-teal-600/15 text-teal-400 border border-teal-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                    <i data-lucide="history" class="w-5 h-5 mr-3 {{ request()->routeIs('historical.*') ? 'text-teal-400' : 'text-slate-400' }}"></i>
                    Historical Records
                </a>

                <div class="pt-4 pb-2">
                    <div class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Analysis & Setup</div>
                </div>

                <a href="{{ route('reports.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('reports.*') ? 'bg-teal-600/15 text-teal-400 border border-teal-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                    <i data-lucide="bar-chart-3" class="w-5 h-5 mr-3 {{ request()->routeIs('reports.*') ? 'text-teal-400' : 'text-slate-400' }}"></i>
                    Reports & Analytics
                </a>

                <a href="{{ route('settings.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('settings.*') ? 'bg-teal-600/15 text-teal-400 border border-teal-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                    <i data-lucide="sliders" class="w-5 h-5 mr-3 {{ request()->routeIs('settings.*') ? 'text-teal-400' : 'text-slate-400' }}"></i>
                    Rental Settings
                </a>
            </nav>

            <!-- Bottom Information Card -->
            <div class="p-4 border-t border-slate-800/80 bg-navy-950/30">
                <div class="bg-slate-800/60 border border-slate-700/50 rounded-xl p-3 text-xs">
                    <div class="flex items-center justify-between text-slate-400 mb-1">
                        <span>Configured Rent</span>
                        <span class="text-teal-400 font-semibold">NPR 16,000</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-400">
                        <span>Electricity Rate</span>
                        <span class="text-slate-200 font-medium">NPR 10 / unit</span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top Header -->
            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 lg:px-8 shrink-0 no-print">
                <div class="flex items-center gap-3">
                    <button type="button" class="md:hidden text-slate-600 hover:text-slate-900 focus:outline-none" onclick="toggleSidebar()">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>
                    <h1 class="text-xl font-bold text-slate-800 tracking-tight">@yield('page_title', 'Dashboard')</h1>
                </div>

                <div class="flex items-center gap-3">
                    <button type="button" onclick="openGlobalPaymentModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-teal-600 hover:bg-teal-700 rounded-lg shadow-sm transition-all focus:ring-2 focus:ring-teal-500 focus:ring-offset-1">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Record Payment</span>
                    </button>
                    <a href="{{ route('monthly-records.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-300 rounded-lg transition-all">
                        <i data-lucide="calendar-plus" class="w-4 h-4 text-slate-500"></i>
                        <span>New Month</span>
                    </a>
                </div>
            </header>

            <!-- Notification Messages -->
            <div class="px-4 sm:px-6 lg:px-8 pt-4 no-print">
                @if(session('success'))
                    <div class="mb-4 flex items-center justify-between p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm shadow-sm">
                        <div class="flex items-center gap-3">
                            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 shrink-0"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 flex items-center justify-between p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm shadow-sm">
                        <div class="flex items-center gap-3">
                            <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-600 shrink-0"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm shadow-sm">
                        <div class="flex items-center gap-2 font-semibold mb-1">
                            <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 shrink-0"></i>
                            <span>Please fix the following validation errors:</span>
                        </div>
                        <ul class="list-disc list-inside text-xs space-y-1 ml-6 text-rose-700">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <!-- Page Body -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Global Record Payment Modal -->
    <div id="global-payment-modal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-teal-100 text-teal-700 flex items-center justify-center">
                        <i data-lucide="wallet" class="w-4 h-4"></i>
                    </div>
                    <h3 class="font-bold text-slate-800">Record a Payment</h3>
                </div>
                <button type="button" onclick="closeGlobalPaymentModal()" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form action="{{ route('payments.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Select Rental Month *</label>
                    <select name="monthly_record_id" id="payment_modal_month" required class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 bg-white px-3 py-2 border">
                        @foreach(\App\Models\MonthlyRecord::orderBy('record_date', 'desc')->get() as $rec)
                            <option value="{{ $rec->id }}">
                                {{ $rec->formatted_month }} (Due: NPR {{ number_format($rec->balance, 2) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Amount (NPR) *</label>
                        <input type="number" step="0.01" min="1" name="amount" required placeholder="16000.00" class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 px-3 py-2 border">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Payment Date *</label>
                        <input type="date" name="payment_date" required value="{{ date('Y-m-d') }}" class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 px-3 py-2 border">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Payment Method *</label>
                    <select name="payment_method" required class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 bg-white px-3 py-2 border">
                        <option value="eSewa">eSewa</option>
                        <option value="Khalti">Khalti</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Cash">Cash</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Transaction Ref / Cheque No. (Optional)</label>
                    <input type="text" name="transaction_reference" placeholder="e.g. ESEWA-982187 or Cheque #1029" class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 px-3 py-2 border">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Notes / Description (Optional)</label>
                    <input type="text" name="notes" placeholder="e.g. 1st installment or full rent cleared" class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 px-3 py-2 border">
                </div>

                <div class="pt-2 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeGlobalPaymentModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-teal-600 hover:bg-teal-700 rounded-lg shadow-sm">Save Payment</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Generic Confirmation Delete Modal -->
    <div id="delete-confirm-modal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 text-center shadow-2xl border border-slate-200">
            <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 mx-auto flex items-center justify-center mb-4">
                <i data-lucide="alert-triangle" class="w-6 h-6"></i>
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-1" id="delete-modal-title">Confirm Deletion</h3>
            <p class="text-xs text-slate-500 mb-6" id="delete-modal-desc">Are you sure you want to delete this record? This action cannot be undone.</p>
            <form id="delete-modal-form" method="POST" class="flex items-center justify-center gap-3">
                @csrf
                @method('DELETE')
                <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-lg shadow-sm">Delete</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('mobile-sidebar-backdrop');
            sidebar.classList.toggle('-translate-x-full');
            backdrop.classList.toggle('hidden');
        }

        function openGlobalPaymentModal(recordId = null) {
            const modal = document.getElementById('global-payment-modal');
            if (recordId) {
                const select = document.getElementById('payment_modal_month');
                if (select) select.value = recordId;
            }
            modal.classList.remove('hidden');
            if (window.lucide) window.lucide.createIcons();
        }

        function closeGlobalPaymentModal() {
            document.getElementById('global-payment-modal').classList.add('hidden');
        }

        function confirmDelete(actionUrl, message = null) {
            const modal = document.getElementById('delete-confirm-modal');
            const form = document.getElementById('delete-modal-form');
            form.action = actionUrl;
            if (message) {
                document.getElementById('delete-modal-desc').innerText = message;
            }
            modal.classList.remove('hidden');
            if (window.lucide) window.lucide.createIcons();
        }

        function closeDeleteModal() {
            document.getElementById('delete-confirm-modal').classList.add('hidden');
        }
    </script>
    @stack('scripts')
</body>
</html>
