@props(['action'])

<div class="sticky top-0 z-30 bg-slate-100/95 backdrop-blur-sm py-2 px-4 -mx-4 mb-4 border-b border-slate-200/60">
    <form method="GET" action="{{ $action }}" class="flex gap-2 w-full">
        
        {{-- Tombol Sort --}}
        <button 
            type="button" 
            @click="$dispatch('open-bottom-sheet', 'sort-sheet')"
            class="h-10 w-10 shrink-0 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 active:scale-95 transition {{ request('sort') ? 'border-teal-500 text-teal-600 bg-teal-50' : '' }}"
        >
            <x-lucide-arrow-up-down class="w-5 h-5" />
        </button>

        {{-- Input Search --}}
        <div class="relative flex-1">
            <x-lucide-search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
            <input 
                type="text" 
                name="q" 
                value="{{ request('q') }}"
                placeholder="Cari data..." 
                class="block w-full h-10 pl-9 pr-3 rounded-lg border-slate-200 text-sm focus:border-teal-500 focus:ring-teal-500 shadow-sm"
                onchange="this.form.submit()"
            >
        </div>

        {{-- Tombol Filter --}}
        <button 
            type="button" 
            @click="$dispatch('open-bottom-sheet', 'filter-sheet')"
            class="h-10 w-10 shrink-0 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 active:scale-95 transition relative {{ request('role') || request('status') ? 'border-teal-500 text-teal-600 bg-teal-50' : '' }}"
        >
            <x-lucide-filter class="w-5 h-5" />
            {{-- Dot Indikator jika ada filter aktif --}}
            @if(request('role') || request('status'))
                <span class="absolute top-2 right-2 w-1.5 h-1.5 bg-teal-500 rounded-full"></span>
            @endif
        </button>
    </form>
</div>