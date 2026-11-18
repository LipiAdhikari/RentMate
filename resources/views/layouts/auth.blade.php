<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'RentMate')</title>
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
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
    @stack('styles')
</head>
<body class="h-full flex flex-col text-slate-800 antialiased font-sans">

    @section('header')
    <!-- Top Brand Bar -->
    <header class="bg-white border-b border-slate-200 px-6 py-4 shrink-0">
        <div class="max-w-md mx-auto flex items-center gap-2">
            <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-teal-500 to-teal-400 flex items-center justify-center text-white shadow-sm shadow-teal-500/30">
                <i data-lucide="home" class="w-5 h-5"></i>
            </div>
            <span class="text-lg font-bold tracking-tight text-slate-900">Rent<span class="text-teal-600">Mate</span></span>
        </div>
    </header>
    @show

    <!-- Page Content -->
    <main class="flex-1 flex items-center justify-center px-4 sm:px-6 lg:px-8 pt-6 shrink-0">
        <div class="w-full max-w-md">
            @if(session('success'))
                <div class="mb-6 flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-start gap-3">
                    <i data-lucide="alert-circle" class="w-4 h-4 mt-0.5 shrink-0 text-rose-600"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                    <h4 class="text-xs font-semibold text-rose-900 mb-2 uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="alert-triangle" class="w-3 h-3"></i>
                        Please fix the following:
                    </h4>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 px-6 py-4 shrink-0">
        <div class="max-w-md mx-auto flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-5 h-5 rounded-lg bg-teal-500/10 text-teal-600 flex items-center justify-center">
                    <i data-lucide="shield-check" class="w-3 h-3"></i>
                </div>
                <span class="text-xs text-slate-500">RentMate &copy; {{ date('Y') }}</span>
            </div>
            <div class="flex items-center gap-4 text-xs text-slate-400">
                <a href="#" class="hover:text-teal-600 transition">Privacy</a>
                <a href="#" class="hover:text-teal-600 transition">Terms</a>
                <a href="#" class="hover:text-teal-600 transition">Contact</a>
            </div>
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
