@props([
    'action',
    'search' => '',
    'sort' => null,
    'direction' => null,
    'placeholder' => 'Cari data...',
    'filters' => [],
])

@php
    $filterKeys = array_keys($filters);

    $activeStates = collect($filterKeys)->mapWithKeys(function ($key) {
        $value = request()->query($key);

        if (is_array($value)) {
            $isActive = collect($value)
                ->filter(fn ($val) => $val !== null && $val !== '')
                ->isNotEmpty();
        } else {
            $isActive = $value !== null && $value !== '';
        }

        return [$key => $isActive];
    });

    $activeFilterCount = $activeStates->filter()->count();
    $hasActiveFilter = $activeFilterCount > 0;
    $shouldShowFilters = $hasActiveFilter || request('filters_visible') === 'true';
@endphp

<form 
    method="GET"
    action="{{ $action }}"
    class="w-full space-y-3"
    x-data="{
        filtersVisible: {{ $shouldShowFilters ? 'true' : 'false' }},
        activeMap: @js($activeStates),
        activeCount: {{ $activeFilterCount }},

        clearAllFilters() {
            const container = this.$refs.filters;
            if (!container) return;

            const triggerChange = (el) => el.dispatchEvent(new Event('change', { bubbles: true }));

            const searchInput = this.$el.querySelector('input[name=q]');
            if (searchInput) {
                searchInput.value = '';
            }

            container.querySelectorAll('input[type=checkbox]').forEach(el => {
                if (el.checked) {
                    el.checked = false;
                    triggerChange(el);
                }
            });

            container.querySelectorAll('select').forEach(select => {
                let changed = false;

                if (select.multiple) {
                    Array.from(select.options).forEach(opt => {
                        if (opt.selected) {
                            opt.selected = false;
                            changed = true;
                        }
                    });
                } else if (select.selectedIndex !== -1) {
                    select.selectedIndex = -1;
                    changed = true;
                }

                if (changed) {
                    triggerChange(select);
                }
            });

            const params = new URLSearchParams();
            
            params.append('filters_visible', 'true');

            this.$el.querySelectorAll('input[type=hidden]').forEach(input => {
                if (container.contains(input)) return;
                
                if (input.name && input.value) {
                    params.append(input.name, input.value);
                }
            });

            const baseUrl = this.$el.getAttribute('action') || window.location.pathname;
            const query = params.toString();
            const target = query ? `${baseUrl}?${query}` : baseUrl;

            window.location.href = target;
        },

        updateActive(detail) {
            if (!detail || !detail.name) return;

            this.activeMap[detail.name] = !!detail.active;
            this.activeCount = Object.values(this.activeMap).filter(Boolean).length;
        }
    }"
    @filter-chip-activity.window="updateActive($event.detail)"
>
    <div class="flex items-center justify-between gap-4">
        <div class="w-full md:max-w-md">
            <x-search-bar
                :value="$search"
                :placeholder="$placeholder"
                name="q"
            />
        </div>

        @if(!empty($filters))
            <x-action-button
                type="button"
                variant="secondary"
                icon="filter"
                @click="filtersVisible = !filtersVisible"
                x-bind:class="filtersVisible || activeCount > 0
                    ? 'bg-teal-50 border-2 border-teal-500 text-teal-600'
                    : ''"
            >
                Filter
                <span 
                    x-show="activeCount > 0"
                    x-text="activeCount"
                    class="ml-2 inline-flex items-center justify-center h-5 px-2 rounded-full bg-teal-600 text-white text-[11px] font-bold"
                ></span>
            </x-action-button>
        @endif

        @if($sort) <input type="hidden" name="sort" value="{{ $sort }}"> @endif
        @if($direction) <input type="hidden" name="direction" value="{{ $direction }}"> @endif
        
        <input type="hidden" name="filters_visible" :value="filtersVisible ? 'true' : 'false'">
        {{ $slot }}
    </div>

    @if(!empty($filters))
        <div 
            class="flex flex-wrap items-center gap-2"
            x-ref="filters"
            x-show="filtersVisible"
            x-transition
            x-cloak
        >
            
            @foreach($filters as $key => $label)
                @php $slotName = 'filter_' . $key; @endphp
                <x-filter-chip :label="$label" :name="$key">
                    @if(isset($$slotName))
                        {{ $$slotName }}
                    @else
                        <p class="text-xs text-slate-400 italic">Slot filter_{{ $key }} tidak ditemukan.</p>
                    @endif
                </x-filter-chip>
            @endforeach

            @if($hasActiveFilter)
                <button 
                    type="button" 
                    @click="clearAllFilters()"
                    class="ml-2 text-xs text-slate-500 hover:text-red-600 font-medium hover:underline flex items-center gap-1 transition-colors"
                >
                    <x-lucide-trash-2 class="w-3 h-3" />
                    Clear all
                </button>
            @endif

        </div>
    @endif
</form>
