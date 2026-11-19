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
                        <span class="text-[10px] text-slate-400 uppercase tracking-widest block font-medium">Bilingual BS / AD</span>
                    </div>
                </a>
                <button class="md:hidden text-slate-400 hover:text-white" onclick="toggleSidebar()">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-3 py-5 space-y-1 overflow-y-auto">
                @auth
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
                    Payments & Timing
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

                <div class="pt-4 pb-2">
                    <div class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Account</div>
                </div>

                <a href="{{ route('profile.show') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('profile.*') ? 'bg-teal-600/15 text-teal-400 border border-teal-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                    <i data-lucide="user" class="w-5 h-5 mr-3 {{ request()->routeIs('profile.*') ? 'text-teal-400' : 'text-slate-400' }}"></i>
                    My Profile
                </a>

                <form action="{{ route('logout') }}" method="POST" class="flex items-center px-3 py-2.5">
                    @csrf
                    <button type="submit" class="flex items-center px-3 py-2.5 text-sm font-medium text-rose-400 hover:text-rose-300 hover:bg-rose-900/20 rounded-lg transition w-full">
                        <i data-lucide="log-out" class="w-5 h-5 mr-3"></i>
                        Logout
                    </button>
                </form>

                @else
                <div class="px-4 mb-4 bg-navy-950/60 rounded-xl p-4 border border-slate-700/50">
                    <h3 class="text-sm font-bold text-white mb-2">Welcome to RentMate</h3>
                    <p class="text-xs text-slate-400 mb-3">Track your rent, utilities, and payments.</p>
                    <a href="{{ route('login') }}" class="block w-full py-2.5 text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 rounded-lg shadow-sm shadow-teal-900/20 transition text-center">
                        Sign In
                    </a>
                    <a href="{{ route('register') }}" class="mt-2 block w-full py-2 text-xs font-semibold text-slate-300 hover:text-white border border-slate-600 rounded-lg transition text-center">
                        Create Account
                    </a>
                </div>
                @endauth
            </nav>

            <!-- Bottom Information Card -->
            <div class="p-4 border-t border-slate-800/80 bg-navy-950/30">
                <div class="bg-slate-800/60 border border-slate-700/50 rounded-xl p-3 text-xs">
                    <div class="text-[11px] text-teal-400 font-bold mb-1 flex items-center gap-1">
                        <span>🇳🇵 Bikram Sambat Active</span>
                    </div>
                    <div class="text-[11px] text-slate-300 font-medium">
                        {{ \App\Services\NepaliCalendar::today()['formatted_np'] }}
                    </div>
                    <div class="text-[10px] text-slate-400 mt-0.5">
                        {{ date('F d, Y') }}
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
                    <div>
                        <h1 class="text-xl font-bold text-slate-800 tracking-tight">@yield('page_title', 'Dashboard')</h1>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Live Dual Calendar Pill -->
                    <div class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 rounded-lg text-xs font-semibold text-slate-700 border border-slate-200">
                        <span class="text-rose-600 font-bold">🇳🇵</span>
                        <span>{{ \App\Services\NepaliCalendar::today()['formatted'] }}</span>
                        <span class="text-slate-400">({{ date('M d') }})</span>
                    </div>

                    <button type="button" onclick="openGlobalPaymentModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-teal-600 hover:bg-teal-700 rounded-lg shadow-sm transition focus:ring-2 focus:ring-teal-500 focus:ring-offset-1">
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
                            <span class="font-medium">{{ session('success') }}</span>
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

    <!-- Global Record Payment Modal with Auto-Detection & Dual Calendars -->
    <div id="global-payment-modal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-teal-100 text-teal-700 flex items-center justify-center">
                        <i data-lucide="wallet" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Record Rental Payment</h3>
                        <p class="text-[11px] text-slate-500">Auto-detects paid rent & late days (e.g. Jan rent paid Feb 15)</p>
                    </div>
                </div>
                <button type="button" onclick="closeGlobalPaymentModal()" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form action="{{ route('payments.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Rental Month to Clear *</label>
                    <select name="monthly_record_id" id="payment_modal_month" class="w-full text-xs rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 bg-white px-3 py-2 border font-medium">
                        <option value="auto" selected>⚡ Auto-Apply to Oldest Unpaid Month (Recommended)</option>
                        @foreach(\App\Models\MonthlyRecord::where('user_id', auth()->id())->orderBy('record_date', 'desc')->limit(20)->get() as $rec)
                            <option value="{{ $rec->id }}">
                                {{ $rec->dual_month_label }} — Due: NPR {{ number_format($rec->balance, 2) }}
                            </option>
                        @endforeach
                    </select>
                    <span class="text-[10px] text-teal-600 mt-1 block">
                        Even if paid late in another month, selecting that month marks that 1 month rent has been cleared.
                    </span>
                </div>

                @php
                    $todayBs = \App\Services\NepaliCalendar::today();
                @endphp
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Amount (NPR) *</label>
                        <input type="number" step="0.01" min="1" name="amount" required placeholder="16000.00" class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 px-3 py-2 border font-medium">
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-semibold text-slate-700">Payment Date (Bikram Sambat / वि.सं.) *</label>
                            <span class="text-[10px] font-semibold text-teal-600 bg-teal-50 px-2 py-0.5 rounded border border-teal-200/50">नेपाली क्यालेन्डर</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Year (वर्ष)</label>
                                <select name="bs_year" id="global_bs_year" onchange="syncGlobalBsDate()" class="w-full text-xs rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 bg-white px-2 py-2 border font-semibold text-slate-700">
                                    @for($y = 2080; $y <= 2086; $y++)
                                        <option value="{{ $y }}" {{ ($todayBs['year'] ?? 2083) == $y ? 'selected' : '' }}>{{ $y }} BS</option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Month (महिना)</label>
                                <select name="bs_month" id="global_bs_month" onchange="syncGlobalBsDate()" class="w-full text-xs rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 bg-white px-2 py-2 border font-semibold text-slate-700">
                                    <option value="1" {{ ($todayBs['month'] ?? 8) == 1 ? 'selected' : '' }}>01 Baisakh (बैशाख)</option>
                                    <option value="2" {{ ($todayBs['month'] ?? 8) == 2 ? 'selected' : '' }}>02 Jestha (जेठ)</option>
                                    <option value="3" {{ ($todayBs['month'] ?? 8) == 3 ? 'selected' : '' }}>03 Ashadh (असार)</option>
                                    <option value="4" {{ ($todayBs['month'] ?? 8) == 4 ? 'selected' : '' }}>04 Shrawan (साउन)</option>
                                    <option value="5" {{ ($todayBs['month'] ?? 8) == 5 ? 'selected' : '' }}>05 Bhadra (भदौ)</option>
                                    <option value="6" {{ ($todayBs['month'] ?? 8) == 6 ? 'selected' : '' }}>06 Ashwin (असोज)</option>
                                    <option value="7" {{ ($todayBs['month'] ?? 8) == 7 ? 'selected' : '' }}>07 Kartik (कार्तिक)</option>
                                    <option value="8" {{ ($todayBs['month'] ?? 8) == 8 ? 'selected' : '' }}>08 Mangsir (मंसिर)</option>
                                    <option value="9" {{ ($todayBs['month'] ?? 8) == 9 ? 'selected' : '' }}>09 Poush (पुष)</option>
                                    <option value="10" {{ ($todayBs['month'] ?? 8) == 10 ? 'selected' : '' }}>10 Magh (माघ)</option>
                                    <option value="11" {{ ($todayBs['month'] ?? 8) == 11 ? 'selected' : '' }}>11 Falgun (फागुन)</option>
                                    <option value="12" {{ ($todayBs['month'] ?? 8) == 12 ? 'selected' : '' }}>12 Chaitra (चैत)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Day (गते)</label>
                                <select name="bs_day" id="global_bs_day" onchange="syncGlobalBsDate()" class="w-full text-xs rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 bg-white px-2 py-2 border font-semibold text-slate-700">
                                    @for($d = 1; $d <= 32; $d++)
                                        <option value="{{ $d }}" {{ ($todayBs['day'] ?? 3) == $d ? 'selected' : '' }}>Gate {{ sprintf('%02d', $d) }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <!-- Hidden synced AD payment_date -->
                        <input type="hidden" name="payment_date" id="global_payment_date" value="{{ date('Y-m-d') }}">

                        <!-- Dynamic Sync Badge -->
                        <div class="mt-2 flex items-center justify-between px-3 py-1.5 rounded-lg bg-teal-50/90 border border-teal-200/60 text-xs text-teal-900">
                            <span class="flex items-center gap-1.5 font-medium">
                                <i data-lucide="calendar" class="w-3.5 h-3.5 text-teal-600 shrink-0"></i>
                                <span>वि.सं.: <strong id="global_bs_label_preview">{{ $todayBs['formatted'] }} ({{ $todayBs['month_np'] }})</strong></span>
                            </span>
                            <span class="text-[11px] text-slate-500 font-mono">
                                Converted AD: <span id="global_ad_label_preview">{{ date('Y-m-d') }}</span>
                            </span>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Payment Method *</label>
                    <select name="payment_method" required class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 bg-white px-3 py-2 border">
                        <option value="eSewa">eSewa</option>
                        <option value="Khalti">Khalti</option>
                        <option value="Bank Transfer">Bank Transfer (Fonepay / Wire)</option>
                        <option value="Cash">Cash Handover</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Transaction Ref / Cheque No. (Optional)</label>
                    <input type="text" name="transaction_reference" placeholder="e.g. ESEWA-982187 or Bank Ref" class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 px-3 py-2 border">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Notes / Remarks (Optional)</label>
                    <input type="text" name="notes" placeholder="e.g. January rent cleared late on Feb 15" class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 px-3 py-2 border">
                </div>

                <div class="pt-2 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeGlobalPaymentModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 rounded-lg shadow-sm">Save & Detect Clearance</button>
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

        /* Bikram Sambat (BS) to AD & AD to BS Converter */
        const bsDaysMap = {
            2078: [31, 31, 31, 32, 31, 31, 30, 29, 30, 29, 30, 30],
            2079: [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
            2080: [31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 30],
            2081: [31, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
            2082: [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
            2083: [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
            2084: [31, 31, 32, 31, 31, 30, 30, 30, 29, 30, 30, 30],
            2085: [31, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
            2086: [31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30]
        };

        const nepaliMonthInfo = [
            null,
            { en: 'Baisakh', np: 'बैशाख' },
            { en: 'Jestha', np: 'जेठ' },
            { en: 'Ashadh', np: 'असार' },
            { en: 'Shrawan', np: 'साउन' },
            { en: 'Bhadra', np: 'भदौ' },
            { en: 'Ashwin', np: 'असोज' },
            { en: 'Kartik', np: 'कार्तिक' },
            { en: 'Mangsir', np: 'मंसिर' },
            { en: 'Poush', np: 'पुष' },
            { en: 'Magh', np: 'माघ' },
            { en: 'Falgun', np: 'फागुन' },
            { en: 'Chaitra', np: 'चैत' }
        ];

        function nepaliBsToAd(bsYear, bsMonth, bsDay) {
            const anchorYear = 2083;
            const anchorDate = new Date(Date.UTC(2026, 3, 14)); // 2026-04-14
            let diffDays = 0;

            if (bsYear >= anchorYear) {
                for (let y = anchorYear; y < bsYear; y++) {
                    const days = (bsDaysMap[y] || [31,31,32,31,31,31,30,29,30,29,30,30]).reduce((a,b)=>a+b, 0);
                    diffDays += days;
                }
                for (let m = 1; m < bsMonth; m++) {
                    diffDays += (bsDaysMap[bsYear] ? bsDaysMap[bsYear][m-1] : 30);
                }
                diffDays += (bsDay - 1);
                const target = new Date(anchorDate.getTime() + diffDays * 86400000);
                return target.toISOString().split('T')[0];
            } else {
                for (let y = anchorYear - 1; y > bsYear; y--) {
                    const days = (bsDaysMap[y] || [31,31,32,31,31,31,30,29,30,29,30,30]).reduce((a,b)=>a+b, 0);
                    diffDays += days;
                }
                for (let m = bsMonth + 1; m <= 12; m++) {
                    diffDays += (bsDaysMap[bsYear] ? bsDaysMap[bsYear][m-1] : 30);
                }
                const maxDays = bsDaysMap[bsYear] ? bsDaysMap[bsYear][bsMonth-1] : 30;
                diffDays += (maxDays - bsDay + 1);
                const target = new Date(anchorDate.getTime() - diffDays * 86400000);
                return target.toISOString().split('T')[0];
            }
        }

        function nepaliAdToBs(adDateStr) {
            const anchorDate = new Date(Date.UTC(2026, 3, 14));
            const parts = adDateStr.split('-');
            const d = new Date(Date.UTC(parseInt(parts[0]), parseInt(parts[1])-1, parseInt(parts[2])));
            let diffDays = Math.round((d.getTime() - anchorDate.getTime()) / 86400000);

            let bsYear = 2083;
            let bsMonth = 1;
            let bsDay = 1 + diffDays;

            if (bsDay > 0) {
                while (bsDay > (bsDaysMap[bsYear] ? bsDaysMap[bsYear][bsMonth - 1] : 30)) {
                    bsDay -= (bsDaysMap[bsYear] ? bsDaysMap[bsYear][bsMonth - 1] : 30);
                    bsMonth++;
                    if (bsMonth > 12) {
                        bsMonth = 1;
                        bsYear++;
                    }
                }
            } else {
                while (bsDay <= 0) {
                    bsMonth--;
                    if (bsMonth < 1) {
                        bsMonth = 12;
                        bsYear--;
                    }
                    bsDay += (bsDaysMap[bsYear] ? bsDaysMap[bsYear][bsMonth - 1] : 30);
                }
            }
            return { year: bsYear, month: bsMonth, day: bsDay };
        }

        function syncGlobalBsDate() {
            const yEl = document.getElementById('global_bs_year');
            const mEl = document.getElementById('global_bs_month');
            const dEl = document.getElementById('global_bs_day');
            if (!yEl || !mEl || !dEl) return;

            const y = parseInt(yEl.value);
            const m = parseInt(mEl.value);
            const d = parseInt(dEl.value);

            const adDate = nepaliBsToAd(y, m, d);
            const hiddenAdInput = document.getElementById('global_payment_date');
            if (hiddenAdInput) hiddenAdInput.value = adDate;

            const mInfo = nepaliMonthInfo[m] || { en: 'Month', np: '' };
            const bsLabel = `${mInfo.en} ${String(d).padStart(2, '0')}, ${y} (${mInfo.np})`;

            const bsPreview = document.getElementById('global_bs_label_preview');
            const adPreview = document.getElementById('global_ad_label_preview');
            if (bsPreview) bsPreview.innerText = bsLabel;
            if (adPreview) adPreview.innerText = adDate;
        }
    </script>
    @stack('scripts')
</body>
</html>
