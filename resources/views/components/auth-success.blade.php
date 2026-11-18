<!-- Authentication Success Notification Component -->
@props(['message' => session('success') ?? null])

@if($message)
    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-start gap-3 shadow-sm">
        <i data-lucide="check-circle" class="w-4 h-4 mt-0.5 shrink-0 text-emerald-600"></i>
        <span>{{ $message }}</span>
    </div>
@endif
