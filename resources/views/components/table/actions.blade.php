<div 
    x-data="{ 
        open: false,
        id: 'dropdown-' + Math.random().toString(36).substr(2, 9) 
    }"
    @close-other-dropdowns.window="if($event.detail.id !== id) open = false"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
    
    class="relative inline-block text-left"
>
    {{-- Tombol Titik Tiga --}}
    <button
        @click.stop="open = !open; if(open) $dispatch('close-other-dropdowns', { id: id })"
        type="button"
        class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-200 transition-colors"
        :class="open ? 'bg-slate-200 text-slate-600' : ''"
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