<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Error - RentMate</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 500: '#14b8a6', 600: '#0d9488' },
                        navy: { 900: '#0f172a' },
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 text-slate-900 font-sans flex items-center justify-center min-h-screen">
    <div class="max-w-md w-full px-6 py-8">
        <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-2xl text-center">
            <div class="w-12 h-12 rounded-full bg-red-500/10 text-red-600 flex items-center justify-center mx-auto mb-6">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold mb-2">Server Error</h1>
            @if (isset($message))
                <p class="text-slate-600 mb-6">{{ $message }}</p>
            @else
                <p class="text-slate-600 mb-6">Something went wrong. Please try again later.</p>
            @endif
            <a href="{{ route('landing') }}" class="px-6 py-3 bg-gradient-to-r from-teal-600 to-teal-700 text-white rounded-xl font-bold hover:from-teal-700 hover:to-teal-800 transition-all">
                Return Home
            </a>
        </div>
    </div>
</body>
</html>
