@props(['paginator'])

@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $paginator */
    $perPage = $paginator->perPage();
    $capacityOptions = [10, 25, 50, 100];
    $lastPage = $paginator->lastPage();

    // Helper URL
    $makeUrl = function ($page = null, $newPerPage = null) use ($paginator) {
        $query = request()->except(['page', 'per_page']);
        $path = $paginator->path();
        $query['page'] = $page ?? $paginator->currentPage();
        if ($newPerPage !== null) {
            $query['per_page'] = $newPerPage;
            $query['page'] = 1;
        } else {
            $query['per_page'] = $paginator->perPage();
        }
        return $path . '?' . http_build_query($query);
    };

    $perPageUrl = function ($capacity) use ($makeUrl) {
        return $makeUrl(1, $capacity);
    };

    $capacityOptionsData = [];
    foreach ($capacityOptions as $cap) {
        // Key = URL, Label = Angka
        $capacityOptionsData[$perPageUrl($cap)] = $cap;
    }
@endphp

<div x-data="{ 
        pageInput: '{{ $paginator->currentPage() }}',
        lastPage: {{ $lastPage }},
        goToPage() {
            let page = parseInt(this.pageInput);
            if (isNaN(page) || page < 1 || page > this.lastPage) {
                alert(`Halaman harus antara 1 dan ${this.lastPage}.`);
                this.pageInput = '{{ $paginator->currentPage() }}';
                return;
            }
            window.location.href = '{{ $makeUrl('PAGE_NUMBER') }}'.replace('PAGE_NUMBER', page);
        }
    }"
    class="flex flex-col md:flex-row items-center justify-between text-xs text-slate-500 py-4 border-t border-slate-200 mt-4">

    {{-- KIRI --}}
    <div class="flex items-center gap-4">
        <div class="flex items-center justify-center">
            {{ $paginator->links('pagination::tailwind') }}
        </div>

        <div class="hidden sm:block text-xs font-medium text-slate-500">
            Menampilkan <span
                class="font-bold text-slate-700">{{ number_format($paginator->firstItem() ?? 0) }}-{{ number_format($paginator->lastItem() ?? 0) }}</span>
            dari <span class="font-bold text-slate-700">{{ number_format($paginator->total()) }}</span> data
        </div>
    </div>

    {{-- KANAN --}}
    <div class="flex items-center gap-3 mt-3 md:mt-0">
        <div class="flex items-center gap-2">
            <x-custom-select width="w-[130px]" prefix-label="Tampilkan:" :options="$capacityOptionsData"
                :value="$perPageUrl($perPage)" :on-change="'window.location.href = key'" drop-up :searchable="false"
                class="h-[42px] flex items-center" />
        </div>

        {{-- Go To Page --}}
        <div class="flex items-center bg-white border border-slate-200 shadow-sm rounded-xl p-1 h-[42px]">
            <span class="pl-3 pr-2 text-xs font-medium text-slate-400 whitespace-nowrap">Ke Hal.</span>

            <input type="number" x-model.lazy="pageInput" @keydown.enter.prevent="goToPage()"
                class="w-10 border-none bg-slate-50 rounded-lg text-center text-xs font-bold text-slate-700 focus:ring-2 focus:ring-teal-500/20 p-1 mx-1 h-7"
                min="1" max="{{ $lastPage }}" />

            <button type="button" @click="goToPage()"
                class="inline-flex items-center justify-center h-7 w-7 text-slate-400 hover:text-teal-600 hover:bg-slate-100 rounded-lg transition-colors ml-1"
                title="Pergi">
                <x-lucide-arrow-right class="h-3.5 w-3.5" />
            </button>
        </div>
    </div>
</div>