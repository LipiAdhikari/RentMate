<!-- Authentication Primary Button Component -->
@props(['type' => 'submit', 'text' => 'Continue', 'loading' => false, 'class' => null, 'disabled' => false])

<button
    type="{{ $type }}"
    {{ $disabled ? 'disabled' : '' }}
    {{ $loading ? 'disabled' : '' }}
    class="w-full py-3.5 px-4 rounded-xl bg-brand-600 text-white font-semibold text-sm shadow-lg shadow-brand-500/20 hover:bg-brand-700 hover:shadow-brand-500/40 transition-all active:scale-[0.98] flex items-center justify-center gap-2 {{ $class }}"
>
    @if($loading)
        <svg class="w-4 h-4 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938V7.938A7.962 7.962 0 017.938 0L4 4zM7 9h3v3H7z"></path>
        </svg>
    @else
        {{ $text }}
    @endif
</button>
