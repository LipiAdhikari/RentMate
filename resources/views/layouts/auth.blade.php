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
        .glass {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
    </style>
    @stack('styles')
</head>
<body class="h-full flex flex-col text-slate-900 antialiased font-sans">

    @yield('content')

    <!-- Footer - always at bottom -->
    <footer class="bg-white border-t border-slate-200 px-6 py-4 shrink-0">
        <div class="max-w-md mx-auto flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-5 h-5 rounded-lg bg-teal-500/10 text-teal-600 flex items-center justify-center">
                    <i data-lucide="shield-check" class="w-3 h-3"></i>
                </div>
                <span class="text-xs text-slate-500">RentMate &copy; {{ date('Y') }}</span>
            </div>
            <div class="flex items-center gap-4 text-xs text-slate-400">
                <a href="{{ route('landing') }}" class="hover:text-teal-600 transition">Home</a>
                <a href="#" class="hover:text-teal-600 transition">Privacy</a>
                <a href="#" class="hover:text-teal-600 transition">Terms</a>
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
