@if ($paginator->hasPages())
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();

        $pages = [1];

        if ($lastPage > 1) {
            $pages[] = $lastPage;
        }

        for ($i = $currentPage - 1; $i <= $currentPage + 1; $i++) {
            if ($i > 1 && $i < $lastPage) {
                $pages[] = $i;
            }
        }

        $pages = array_values(array_unique($pages));
        sort($pages);
    @endphp

    <div class="flex flex-col items-center justify-center">
        {{-- DESKTOP / TABLET: Sliding window dengan angka & ellipsis --}}
        <div class="hidden sm:flex items-center justify-center text-xs space-x-1 p-1 rounded-xl bg-white border border-slate-300 shadow-sm">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center justify-center h-8 w-8 text-slate-300 cursor-default rounded-xl">
                    <x-lucide-chevron-left class="w-4 h-4" />
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                   class="inline-flex items-center justify-center h-8 w-8 text-slate-600 hover:bg-slate-100 hover:text-slate-900 rounded-xl transition-colors"
                >
                    <x-lucide-chevron-left class="w-4 h-4" />
                </a>
            @endif

            {{-- Page Numbers with Ellipsis --}}
            @php $previous = null; @endphp
            @foreach ($pages as $page)
                @if (!is_null($previous) && $page - $previous > 1)
                    <span class="inline-flex items-center justify-center h-8 w-8 text-slate-400 cursor-default">
                        &hellip;
                    </span>
                @endif

                @if ($page == $currentPage)
                    {{-- AKTIF: Solid Teal, Shadow Halus --}}
                    <span aria-current="page"
                        class="inline-flex items-center justify-center h-8 w-8 rounded-xl text-xs font-bold bg-teal-600 text-white shadow-sm"
                    >
                        {{ $page }}
                    </span>
                @else
                    <a href="{{ $paginator->url($page) }}"
                        class="inline-flex items-center justify-center h-8 w-8 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors"
                        aria-label="{{ __('Go to page :page', ['page' => $page]) }}"
                    >
                        {{ $page }}
                    </a>
                @endif

                @php $previous = $page; @endphp
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                   class="inline-flex items-center justify-center h-8 w-8 text-slate-600 hover:bg-slate-100 hover:text-slate-900 rounded-xl transition-colors"
                >
                    <x-lucide-chevron-right class="w-4 h-4" />
                </a>
            @else
                <span class="inline-flex items-center justify-center h-8 w-8 text-slate-300 cursor-default rounded-xl">
                    <x-lucide-chevron-right class="w-4 h-4" />
                </span>
            @endif
        </div>

        {{-- MOBILE: Hanya Prev / Next + indikator "Halaman X dari Y" --}}
        <div class="flex sm:hidden items-center justify-center space-x-3 text-xs">
            {{-- Prev Button --}}
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center justify-center px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-300 cursor-default">
                    <x-lucide-chevron-left class="w-4 h-4" />
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                   class="inline-flex items-center justify-center px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm active:bg-teal-50"
                >
                    <x-lucide-chevron-left class="w-4 h-4" />
                </a>
            @endif

            {{-- Halaman X dari Y --}}
            <div class="text-xs font-medium text-slate-500">
                Halaman
                <span class="font-bold text-teal-600">{{ $currentPage }}</span>
                <span class="mx-0.5 text-slate-400">/</span>
                <span class="font-semibold text-slate-700">{{ $lastPage }}</span>
            </div>

            {{-- Next Button --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                   class="inline-flex items-center justify-center px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm active:bg-teal-50"
                >
                    <x-lucide-chevron-right class="w-4 h-4" />
                </a>
            @else
                <span class="inline-flex items-center justify-center px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-300 cursor-default">
                    <x-lucide-chevron-right class="w-4 h-4" />
                </span>
            @endif
        </div>
    </div>
@endif
