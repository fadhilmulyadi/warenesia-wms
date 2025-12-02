@props([
    'name',
    'title',
])

<div
    x-data="{ open: false }"
    x-on:open-{{ $name }}.window="open = true"
    x-on:close-{{ $name }}.window="open = false"
    x-on:keydown.escape.window="open = false"
    x-show="open"
    class="relative z-50"
    style="display: none;"
>
    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
        x-on:click="open = false"
    ></div>

    {{-- Sheet --}}
    <div class="fixed inset-x-0 bottom-0 z-10 w-full overflow-hidden bg-white rounded-t-xl shadow-xl ring-1 ring-black ring-opacity-5">
        <div
            x-show="open"
            x-transition:enter="transform transition ease-in-out duration-300 sm:duration-500"
            x-transition:enter-start="translate-y-full"
            x-transition:enter-end="translate-y-0"
            x-transition:leave="transform transition ease-in-out duration-300 sm:duration-500"
            x-transition:leave-start="translate-y-0"
            x-transition:leave-end="translate-y-full"
        >
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                <h3 class="text-lg font-medium text-slate-900">{{ $title }}</h3>
                <button x-on:click="open = false" class="text-slate-400 hover:text-slate-500">
                    <span class="sr-only">Close</span>
                    <x-lucide-x class="w-6 h-6" />
                </button>
            </div>
            <div class="px-4 py-4 max-h-[80vh] overflow-y-auto">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>