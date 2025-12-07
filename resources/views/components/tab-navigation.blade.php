@props([
    'tabs' => [],
    'active' => null,
    'baseUrl' => null,
])

<div 
    x-data="{
        active: '{{ $active }}',
        tabs: {{ count($tabs) }},
        keys: @js(array_keys($tabs)),

        get activeIndex() {
            return this.keys.indexOf(this.active);
        },

        get sliderStyle() {
            const containerPadding = 6; // p-1.5 = 6px
            const totalWidth = `calc(100% - ${containerPadding * 2}px)`;
            const width = `calc(${totalWidth} / ${this.tabs})`;
            const left = `calc(${containerPadding}px + (${totalWidth} / ${this.tabs} * ${this.activeIndex}))`;
            return `width: ${width}; left: ${left};`;
        }
    }"
    class="relative w-full"
>
    <ul class="relative flex p-1.5 list-none rounded-xl bg-slate-100 border border-slate-300">

        {{-- SLIDER --}}
        <div 
            class="absolute z-0 top-1.5 bottom-1.5 rounded-lg bg-white shadow-sm ring-1 ring-black/5 transition-all duration-300 ease-out pointer-events-none"
            :style="sliderStyle"
        ></div>

        {{-- TABS --}}
        @foreach($tabs as $key => $tab)
            @php
                $label = is_array($tab) ? ($tab['label'] ?? $key) : $tab;
                $query = is_array($tab) ? ($tab['query'] ?? []) : [];
                $href = route($baseUrl, array_merge($query, ['tab' => $key]));
            @endphp
            <li class="relative z-10 flex-1 text-center">
                <a
                    href="{{ $href }}"
                    @click="active = '{{ $key }}'"
                    class="block w-full px-2 py-2 text-xs font-medium rounded-lg transition-colors duration-200"
                    :class="active === '{{ $key }}'
                        ? 'text-slate-900 font-semibold'
                        : 'text-slate-500 hover:text-slate-700'"
                >
                    {{ $label }}
                </a>
            </li>
        @endforeach

    </ul>
</div>
