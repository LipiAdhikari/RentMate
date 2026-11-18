<!-- Authentication Validation Errors Component -->
@props(['errors' => $errors ?? []])

@if($errors->any())
    <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm shadow-sm">
        <div class="flex items-start gap-3">
            <i data-lucide="alert-circle" class="w-4 h-4 mt-0.5 shrink-0 text-red-600"></i>
            <div>
                <h4 class="text-xs font-semibold text-red-900 mb-1 uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="alert-triangle" class="w-2.5 h-2.5"></i>
                    Please fix the following:
                </h4>
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif
