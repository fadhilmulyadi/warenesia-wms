@props([
    'items',
    'config' => [],
    'cardView',
    'extraData' => [],
])

@php
    $searchEnabled = $config['search']['enabled'] ?? false;
    $filtersEnabled = !empty($config['filters']);
    $sortEnabled = $config['sort']['enabled'] ?? false;
    $fabEnabled = $config['fab']['enabled'] ?? false;
    $emptyState = $config['empty_state'] ?? [];
    
    $activeFilterCount = 0;
    if ($filtersEnabled) {
        foreach ($config['filters'] as $key => $filter) {
            if ($filter['type'] === 'checkbox-list') {
                if (request()->has($filter['param'])) {
                    $activeFilterCount += count((array) request($filter['param']));
                }
            } elseif ($filter['type'] === 'select') {
                if (request()->filled($filter['param'])) {
                    $activeFilterCount++;
                }
            } elseif ($filter['type'] === 'date-range') {
                if (request()->filled($filter['param'][0]) || request()->filled($filter['param'][1])) {
                    $activeFilterCount++;
                }
            }
        }
    }
@endphp

<div class="pb-24 space-y-4">
    @if($searchEnabled)
        <form action="{{ $emptyState['reset_route'] ?? url()->current() }}" method="GET">
            @foreach($config['hidden_query'] ?? [] as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            
            <div class="relative" x-data="{ query: '{{ request($config['search']['param']) }}' }">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <x-lucide-search class="h-5 w-5 text-slate-400" />
                </div>
                <input
                    type="search"
                    name="{{ $config['search']['param'] }}"
                    x-model="query"
                    class="block w-full pl-10 pr-10 py-2 border border-slate-300 rounded-lg leading-5 bg-white placeholder-slate-500 focus:outline-none focus:placeholder-slate-400 focus:ring-1 focus:ring-teal-500 focus:border-teal-500 sm:text-sm [&::-webkit-search-cancel-button]:hidden"
                    placeholder="{{ $config['search']['placeholder'] }}"
                >
                <button
                    type="button"
                    x-show="query.length > 0"
                    x-transition
                    x-on:click="query = ''; $nextTick(() => $el.closest('form').submit())"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-teal-500 hover:text-teal-600"
                    style="display: none;"
                >
                    <x-lucide-x class="h-5 w-5" />
                </button>
            </div>
        </form>
    @endif

    @if($filtersEnabled || $sortEnabled)
        <div class="flex gap-2 overflow-x-auto pb-1 no-scrollbar">
            @if($filtersEnabled)
                <button
                    x-data
                    x-on:click="$dispatch('open-filter-sheet')"
                    class="flex items-center gap-2 px-3 py-1.5 rounded-full border text-sm font-medium whitespace-nowrap {{ $activeFilterCount > 0 ? 'bg-teal-50 border-teal-200 text-teal-700' : 'bg-white border-slate-300 text-slate-700' }}"
                >
                    <x-lucide-filter class="w-4 h-4" />
                    Filter
                    @if($activeFilterCount > 0)
                        <span class="bg-teal-600 text-white text-[10px] px-1.5 py-0.5 rounded-full">{{ $activeFilterCount }}</span>
                    @endif
                </button>
            @endif

            @if($sortEnabled)
                <button
                    x-data
                    x-on:click="$dispatch('open-sort-sheet')"
                    class="flex items-center gap-2 px-3 py-1.5 rounded-full border bg-white border-slate-300 text-slate-700 text-sm font-medium whitespace-nowrap"
                >
                    <x-lucide-arrow-up-down class="w-4 h-4" />
                    Urutkan
                </button>
            @endif
        </div>
    @endif

    @if($items->isEmpty())
        <div class="flex flex-col items-center justify-center py-12 text-center">
            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                @if(isset($emptyState['icon']))
                    @php
                        $iconName = 'lucide-' . $emptyState['icon'];
                    @endphp
                    <x-dynamic-component :component="$iconName" class="w-8 h-8 text-slate-400" />
                @else
                    <x-lucide-search-x class="w-8 h-8 text-slate-400" />
                @endif
            </div>
            <h3 class="text-sm font-medium text-slate-900">{{ $emptyState['title'] ?? 'Data tidak ditemukan' }}</h3>
            <p class="mt-1 text-sm text-slate-500">{{ $emptyState['description'] ?? '' }}</p>
            @if(isset($emptyState['reset_route']))
                <a href="{{ $emptyState['reset_route'] }}" class="mt-4 text-sm font-medium text-teal-600 hover:text-teal-500">
                    Reset Filter
                </a>
            @endif
        </div>
    @else
        <div class="space-y-3">
            @foreach($items as $item)
                @include($cardView, array_merge(['item' => $item], $extraData))
            @endforeach
        </div>

        @if($items instanceof \Illuminate\Pagination\AbstractPaginator && $items->hasPages())
            <div class="mt-4">
                {{ $items->links() }}
            </div>
        @endif
    @endif

    @if($fabEnabled)
        <a
            href="{{ $config['fab']['route'] }}"
            class="fixed bottom-6 right-6 w-14 h-14 bg-teal-600 text-white rounded-full shadow-lg flex items-center justify-center hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 z-10"
        >
            @php
                $fabIcon = 'lucide-' . ($config['fab']['icon'] ?? 'plus');
            @endphp
            <x-dynamic-component :component="$fabIcon" class="w-6 h-6" />
        </a>
    @endif

    @if($filtersEnabled)
        <x-mobile.bottom-sheet name="filter-sheet" title="Filter Data">
            <form action="{{ $emptyState['reset_route'] ?? url()->current() }}" method="GET" class="space-y-6">
                @if($searchEnabled && request()->filled($config['search']['param']))
                    <input type="hidden" name="{{ $config['search']['param'] }}" value="{{ request($config['search']['param']) }}">
                @endif

                @foreach($config['hidden_query'] ?? [] as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach

                @foreach($config['filters'] as $key => $filter)
                    @if(!$filter['enabled']) @continue @endif

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-900">{{ $filter['label'] }}</label>
                        
                        @if($filter['type'] === 'checkbox-list')
                            <div class="flex flex-wrap gap-2">
                                @foreach($filter['options'] as $value => $label)
                                    @php
                                        $isChecked = in_array($value, (array) request($filter['param'], []));
                                    @endphp
                                    <label
                                        x-data="{ checked: {{ $isChecked ? 'true' : 'false' }} }"
                                        :class="checked ? 'bg-teal-50 border-teal-500 text-teal-700 shadow-sm ring-1 ring-teal-500' : 'bg-white border-slate-200 text-slate-600 hover:border-slate-300 hover:bg-slate-50'"
                                        class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border text-sm font-medium cursor-pointer transition-all duration-200"
                                    >
                                        <input
                                            type="checkbox"
                                            name="{{ $filter['param'] }}[]"
                                            value="{{ $value }}"
                                            class="hidden"
                                            {{ $isChecked ? 'checked' : '' }}
                                            @change="checked = $el.checked"
                                        >
                                        <x-lucide-check x-show="checked" class="w-4 h-4" style="display: none;" />
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                        @elseif($filter['type'] === 'select')
                            <x-custom-select
                                :name="$filter['param']"
                                :options="['' => 'Semua'] + $filter['options']"
                                :value="request($filter['param'])"
                                :placeholder="'Pilih ' . $filter['label']"
                                :searchable="true"
                            />
                        @elseif($filter['type'] === 'date-range')
                            <input type="hidden" name="date_range" value="1">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <input
                                        type="date"
                                        name="{{ $filter['param'][0] }}"
                                        value="{{ request($filter['param'][0]) }}"
                                        class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm"
                                        placeholder="Dari"
                                    >
                                </div>
                                <div>
                                    <input
                                        type="date"
                                        name="{{ $filter['param'][1] }}"
                                        value="{{ request($filter['param'][1]) }}"
                                        class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm"
                                        placeholder="Sampai"
                                    >
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach

                <div class="pt-4 flex gap-3">
                    <a
                        href="{{ $emptyState['reset_route'] ?? url()->current() }}"
                        class="flex-1 px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 text-center"
                    >
                        Reset
                    </a>
                    <button
                        type="submit"
                        class="flex-1 px-4 py-2 bg-teal-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-teal-700"
                    >
                        Terapkan
                    </button>
                </div>
            </form>
        </x-mobile.bottom-sheet>
    @endif

    @if($sortEnabled)
        <x-mobile.bottom-sheet name="sort-sheet" title="Urutkan Data">
            <form action="{{ $emptyState['reset_route'] ?? url()->current() }}" method="GET">
                {{-- Preserve Search --}}
                @if($searchEnabled && request()->filled($config['search']['param']))
                    <input type="hidden" name="{{ $config['search']['param'] }}" value="{{ request($config['search']['param']) }}">
                @endif

                @if($filtersEnabled)
                    @foreach($config['filters'] as $key => $filter)
                        @if(!$filter['enabled']) @continue @endif
                        
                        @if($filter['type'] === 'checkbox-list')
                            @foreach((array) request($filter['param'], []) as $val)
                                <input type="hidden" name="{{ $filter['param'] }}[]" value="{{ $val }}">
                            @endforeach

                        @elseif($filter['type'] === 'select')
                            @if(request()->filled($filter['param']))
                                @php
                                    $selectedValue = request($filter['param']);
                                @endphp
                                @if(is_array($selectedValue))
                                    @foreach($selectedValue as $val)
                                        <input type="hidden" name="{{ $filter['param'] }}[]" value="{{ $val }}">
                                    @endforeach
                                @else
                                    <input type="hidden" name="{{ $filter['param'] }}" value="{{ $selectedValue }}">
                                @endif
                            @endif

                        @elseif($filter['type'] === 'date-range')
                            @if(request()->filled($filter['param'][0]) || request()->filled($filter['param'][1]))
                                <input type="hidden" name="date_range" value="1">
                            @endif

                            @if(request()->filled($filter['param'][0]))
                                <input type="hidden" name="{{ $filter['param'][0] }}" value="{{ request($filter['param'][0]) }}">
                            @endif

                            @if(request()->filled($filter['param'][1]))
                                <input type="hidden" name="{{ $filter['param'][1] }}" value="{{ request($filter['param'][1]) }}">
                            @endif
                        @endif
                    @endforeach
                @endif
                
                @foreach($config['hidden_query'] ?? [] as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach

                <div class="space-y-1">
                    @php
                        $currentSort = request($config['sort']['param'], $config['sort']['default']['field']);
                        $currentDirection = request($config['sort']['direction_param'], $config['sort']['default']['direction']);
                        $currentKey = $currentSort . '|' . $currentDirection;
                    @endphp

                    @foreach($config['sort']['options'] as $option)
                        <button
                            type="submit"
                            name="sort_combined"
                            value="{{ $option['key'] }}"
                            class="w-full flex items-center justify-between px-4 py-3 text-left rounded-lg hover:bg-slate-50 {{ $currentKey === $option['key'] ? 'bg-teal-50 text-teal-700' : 'text-slate-700' }}"
                            x-on:click="$el.form.querySelector('[name=\'{{ $config['sort']['param'] }}\']').value = '{{ $option['field'] }}'; $el.form.querySelector('[name=\'{{ $config['sort']['direction_param'] }}\']').value = '{{ $option['direction'] }}';"
                        >
                            <span class="text-sm font-medium">{{ $option['label'] }}</span>
                            @if($currentKey === $option['key'])
                                <x-lucide-check class="w-4 h-4 text-teal-600" />
                            @endif
                        </button>
                    @endforeach
                    
                    <input type="hidden" name="{{ $config['sort']['param'] }}" value="{{ $currentSort }}">
                    <input type="hidden" name="{{ $config['sort']['direction_param'] }}" value="{{ $currentDirection }}">
                </div>
            </form>
        </x-mobile.bottom-sheet>
    @endif
</div>