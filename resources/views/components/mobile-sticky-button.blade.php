@props([
    'form' => null,
    'icon' => null,
])

<div class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-slate-200 p-3 shadow-lg">
    <button
        type="submit"
        @if($form) form="{{ $form }}" @endif
        class="w-full h-12 rounded-xl bg-teal-600 text-white text-sm font-semibold flex items-center justify-center gap-2 active:scale-[0.98] transition"
    >
        @if($icon)
            <x-dynamic-component :component="'lucide-' . $icon" class="w-4 h-4" />
        @endif

        {{ $slot }}
    </button>
</div>
