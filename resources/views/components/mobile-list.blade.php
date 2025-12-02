@props([
    'items' => [],
    'empty' => 'Tidak ada data.',
    'template' => null,
    'paginate' => null,
])

<div class="md:hidden space-y-3">

    @forelse($items as $item)
        {{-- TEMPLATE DIPASS DARI PARENT --}}
        @include($template, ['item' => $item])

    @empty
        <div class="text-center text-slate-500 py-6 text-sm">
            {{ $empty }}
        </div>
    @endforelse

    {{-- PAGINATION --}}
    @if($paginate)
        <div class="pt-2">
            <x-advanced-pagination :paginator="$paginate" size="small" />
        </div>
    @endif
</div>
