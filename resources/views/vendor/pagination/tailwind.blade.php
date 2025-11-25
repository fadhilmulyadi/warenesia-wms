@if ($paginator->hasPages())
    <div class="flex items-center justify-center text-xs space-x-1 p-1 rounded-xl bg-white border border-slate-200 shadow-sm">
        
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center justify-center h-8 w-8 text-slate-300 cursor-default rounded-lg">
                <x-lucide-chevron-left class="w-4 h-4" />
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" 
               class="inline-flex items-center justify-center h-8 w-8 text-slate-600 hover:bg-slate-100 hover:text-slate-900 rounded-lg transition-colors"
            >
                <x-lucide-chevron-left class="w-4 h-4" />
            </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span class="inline-flex items-center justify-center h-8 w-8 text-slate-400 cursor-default">
                    {{ $element }}
                </span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        {{-- AKTIF: Solid Teal, Shadow Halus --}}
                        <span aria-current="page" 
                            class="inline-flex items-center justify-center h-8 w-8 rounded-lg text-xs font-bold bg-teal-600 text-white shadow-sm"
                        >
                            {{ $page }}
                        </span>
                    @else
                        {{-- TIDAK AKTIF: Clean (Tanpa Border), Hover Effect Only --}}
                        <a href="{{ $url }}" 
                            class="inline-flex items-center justify-center h-8 w-8 rounded-lg text-xs font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors"
                            aria-label="{{ __('Go to page :page', ['page' => $page]) }}"
                        >
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" 
               class="inline-flex items-center justify-center h-8 w-8 text-slate-600 hover:bg-slate-100 hover:text-slate-900 rounded-lg transition-colors"
            >
                <x-lucide-chevron-right class="w-4 h-4" />
            </a>
        @else
            <span class="inline-flex items-center justify-center h-8 w-8 text-slate-300 cursor-default rounded-lg">
                <x-lucide-chevron-right class="w-4 h-4" />
            </span>
        @endif
    </div>
@endif