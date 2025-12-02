@props([
    'form' => null,
])

<div class="md:hidden mt-4">
    <button
        type="submit"
        @if($form) form="{{ $form }}" @endif
        class="w-full h-12 rounded-xl border border-rose-300 text-rose-700 text-sm font-semibold active:scale-[0.98] transition"
    >
        {{ $slot }}
    </button>
</div>
