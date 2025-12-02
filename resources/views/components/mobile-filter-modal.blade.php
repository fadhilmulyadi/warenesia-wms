<div
    x-data="{ open: false }"
    x-on:open-filter-modal.window="open = true"
>
    {{-- Overlay --}}
    <div
        x-show="open"
        x-cloak
        class="fixed inset-0 bg-black/40 z-40"
        x-on:click="open = false"
    ></div>

    {{-- Bottom Sheet --}}
    <div
        x-show="open"
        x-cloak
        class="fixed bottom-0 left-0 right-0 bg-white z-50 rounded-t-2xl p-5 space-y-4 max-h-[80vh] overflow-y-auto"
    >
        <div class="flex justify-between items-center mb-2">
            <h2 class="font-semibold text-slate-900 text-sm">Filter</h2>
            <button class="text-slate-500" x-on:click="open = false">
                Tutup
            </button>
        </div>

        {{-- Filter Bar --}}
        {{ $slot }}
    </div>
</div>
