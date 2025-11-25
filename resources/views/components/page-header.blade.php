@props(['title', 'description' => null])

<div class="flex items-center justify-between w-full">
    <div class="flex flex-col justify-center min-w-0">
        {{-- Judul --}}
        <h1 class="text-2xl font-semibold text-slate-900 ">
            {{ $title }}
        </h1>
        
        {{-- Deskripsi --}}
        @if($description)
            <p class="hidden md:block text-sm text-slate-500 truncate max-w-xl">
                {{ $description }}
            </p>
        @endif
    </div>

    @if($slot->isNotEmpty())
        <div class="flex items-center gap-2 ml-4 shrink-0">
            {{ $slot }}
        </div>
    @endif
</div>