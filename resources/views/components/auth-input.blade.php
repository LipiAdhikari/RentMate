<!-- Authentication Input Component -->
@props(['name', 'label', 'type' => 'text', 'value' => old($name ?? ''), 'placeholder' => null, 'required' => true, 'autocomplete' => null, 'icon' => null, 'error' => null])

<div class="text-left mb-5">
    <label for="{{ $name }}" class="block text-xs font-semibold text-slate-600 mb-2 uppercase tracking-wider">
        {{ $label }}
    </label>
    <div class="relative">
        @if($icon)
            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>
            </div>
        @endif
        <input
            id="{{ $name }}"
            name="{{ $name }}"
            type="{{ $type }}"
            value="{{ $value }}"
            placeholder="{{ $placeholder }}"
            required="{{ $required ? 'true' : 'false' }}"
            autocomplete="{{ $autocomplete ?? old($name) ? 'off' : ($type === 'password' ? 'new-password' : $name) }}"
            class="w-full px-4 py-3.5 rounded-xl border border-gray-200 bg-white focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none transition-all text-sm text-slate-900 placeholder-slate-400 pr-10{{ $error ? ' border-red-500 focus:border-red-500 focus:ring-red-500/20' : '' }}"
        >
    </div>
    @if($error)
        <p class="mt-1.5 text-xs text-red-600">{{ $error }}</p>
    @endif
</div>
