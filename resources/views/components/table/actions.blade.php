<div x-data="{ open: false }" class="relative inline-block text-left">
    {{-- Tombol Titik Tiga --}}
    <button
        @click.stop="open = !open"
        @click.outside="open = false"
        type="button"
        class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-200 transition-colors"
    >
        <x-lucide-more-vertical class="h-4 w-4" />
    </button>

    {{-- Dropdown Menu --}}
    <div 
        x-show="open" 
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 mt-2 w-40 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
        style="display: none;"
        @click.stop
    >
        <div class="py-1 divide-y divide-slate-100">
            {{ $slot }}
        </div>
    </div>
</div>