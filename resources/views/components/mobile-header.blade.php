@props([
    'title',
    'back' => url()->previous(),
    'subtitle' => null,
])

<div class="md:hidden flex items-center gap-3 px-1 py-3 border-b border-slate-100 bg-white">
    <a href="{{ $back }}" class="p-2 -ml-2 active:scale-95 transition">
        <x-lucide-arrow-left class="w-5 h-5 text-slate-700" />
    </a>

    <div class="flex flex-col">
        <h1 class="text-lg font-semibold text-slate-900 leading-none">{{ $title }}</h1>

        @if($subtitle)
            <p class="text-[11px] text-slate-500 mt-0.5">{{ $subtitle }}</p>
        @endif
    </div>
</div>
