@props(['title', 'description' => null])

<div class="flex flex-wrap items-start justify-between gap-3 w-full py-2 sm:py-0">
    <div class="flex flex-col justify-center min-w-0 flex-1">

        <h1 class="font-semibold text-slate-900 leading-tight text-xl sm:text-2xl">
            {{ $title }}
        </h1>

        @if($description)
            <p class="hidden md:block mt-1 text-slate-500 max-w-3xl text-xs sm:text-sm">
                {{ $description }}
            </p>
        @endif
    </div>

    @if($slot->isNotEmpty())
        <div class="flex flex-wrap items-center gap-2 justify-end w-full sm:w-auto mt-2 sm:mt-0">
            {{ $slot }}
        </div>
    @endif
</div>