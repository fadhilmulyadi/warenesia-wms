@props([
    'title' => 'No data found',
    'description' => null,
    'icon' => 'box',
    'actionLabel' => null,
    'actionUrl' => null
])

<div class="flex flex-col items-center justify-center py-12">
    <div class="h-12 w-12 rounded-full bg-slate-100 flex items-center justify-center mb-3">
        <x-dynamic-component :component="'lucide-'.$icon" class="h-6 w-6 text-slate-400" />
    </div>
    <p class="text-sm font-medium text-slate-900">{{ $title }}</p>
    @if($description)
        <p class="text-xs text-slate-500 mt-1">{{ $description }}</p>
    @endif

    @if($actionLabel && $actionUrl)
        <a href="{{ $actionUrl }}"
            class="mt-4 inline-flex items-center rounded-lg bg-teal-600 px-3 py-2 text-xs font-semibold text-white hover:bg-teal-700 transition-colors">
            <x-lucide-plus class="h-3.5 w-3.5 mr-1.5" />
            {{ $actionLabel }}
        </a>
    @endif