<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RentMate')</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#2563eb',
                            600: '#1d4ed8',
                            700: '#1e40af',
                        },
                        slate: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            900: '#172033',
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
        html { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        .input-focus-ring:focus {
            outline: none;
            ring: 2px;
            ring-color: #2563eb;
            ring-opacity: 0.5;
        }
    </style>
    @stack('styles')
</head>
<body class="h-full flex flex-col text-slate-900 antialiased font-sans bg-gray-50">

    <!-- Full Width Header -->
    @yield('header')

    <!-- Centered Main Content -->
    <main class="flex-1">
        <div class="w-full max-w-md mx-auto px-4 sm:px-6 lg:px-8 pt-6">
            @yield('content')
        </div>
    </main>

    <!-- Full Width Footer -->
    <footer class="bg-white border-t border-gray-200 shrink-0">
        <div class="max-w-md mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-5 h-5 rounded-lg bg-brand-100 text-brand-600 flex items-center justify-center">
                    <i data-lucide="shield-check" class="w-3 h-3"></i>
                </div>
                <span class="text-xs text-gray-500">RentMate &copy; {{ date('Y') }}</span>
            </div>
            <div class="flex items-center gap-4 text-xs text-gray-400">
                <a href="{{ route('landing') }}" class="hover:text-brand-600 transition">Home</a>
                <a href="#" class="hover:text-brand-600 transition">Privacy</a>
                <a href="#" class="hover:text-brand-600 transition">Terms</a>
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
