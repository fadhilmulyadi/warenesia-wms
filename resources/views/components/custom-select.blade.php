@props([
    'name' => '',
    'options' => [], 
    'value' => null,
    'placeholder' => 'Pilih opsi',
    'id' => null,
    'required' => false,
    'disabled' => false,
    'searchable' => true,
    'dropUp' => false,
    'width' => 'w-full',
    'onChange' => null,
    'prefixLabel' => null
])

@php
    $id = $id ?? $name;
    $currentValue = (string) $value;
    $initialLabel = array_key_exists($currentValue, $options) ? $options[$currentValue] : '';
@endphp

<div
    x-data="{
        open: false,
        value: @js($currentValue),
        search: @js($initialLabel),
        options: @js($options),
        searchable: @js($searchable),
        
        init() {
            if (this.value && !this.search && this.options[this.value]) {
                this.search = this.options[this.value];
            }
        },

        get filteredOptions() {
            if (!this.searchable) {
                return this.options;
            }

            if (this.search === '') {
                return this.options;
            }

            if (this.value && this.options[this.value] === this.search) {
                return this.options;
            }

            const term = this.search.toLowerCase();
            const result = {};
            
            Object.keys(this.options).forEach(key => {
                const label = String(this.options[key]);
                if (label.toLowerCase().includes(term)) {
                    result[key] = label;
                }
            });
            
            return result;
        },

        select(key, label) {
            this.value = key;
            this.search = label;
            this.open = false;
            
            $dispatch('change', key); 

            const val = key; 
            @if($onChange)
                {!! $onChange !!}
            @endif
        },

        close() {
            this.open = false;
            
            if (!this.searchable) return;

            const exactMatchKey = Object.keys(this.options).find(key => this.options[key] === this.search);
            
            if (exactMatchKey) {
                this.value = exactMatchKey;
            } else if (this.value && this.options[this.value] !== this.search) {
                this.search = this.options[this.value]; // Reset ke nilai lama
            } else if (!this.value && this.search) {
                this.search = ''; // Reset kosong
            }
        },
        
        toggle() {
            if (@js($disabled)) return;
            this.open = !this.open;
            
            if(this.open && this.searchable) {
                $nextTick(() => $refs.searchInput.focus());
            }
        }
    }"
    class="relative {{ $width }} h-[42px]"
    @click.outside="close()"
    wire:ignore.self
>
    <input 
        type="hidden" 
        name="{{ $name }}" 
        :value="value"
        @if($required) required @endif
    >

    {{-- Visual Input --}}
    <div class="relative" @click="toggle()">
        
        @if($prefixLabel)
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="text-slate-500 text-xs mr-1">{{ $prefixLabel }}</span>
            </div>
        @endif

        <input
            x-ref="searchInput"
            type="text"
            x-model="search"
            :readonly="!searchable"
            placeholder="{{ $placeholder }}"
            {{ $disabled ? 'disabled' : '' }}
            class="w-full h-[42px] rounded-xl border-slate-300 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500 disabled:bg-slate-100 disabled:text-slate-500 placeholder:text-slate-400 truncate pr-8"
            :class="{
                'cursor-pointer caret-transparent select-none': !searchable, 
                'cursor-text': searchable,
                'pl-2': !'{{ $prefixLabel }}', 
                'pl-[4.5rem]': '{{ $prefixLabel }}' // Adjust padding if prefix exists
            }"
            autocomplete="off"
        >
        
        <div class="absolute inset-y-0 right-0 flex items-center px-2 text-slate-400 pointer-events-none">
            <x-lucide-chevron-down class="w-4 h-4 transition-transform duration-200" ::class="open ? 'rotate-180' : ''" />
        </div>
    </div>

    {{-- Dropdown List --}}
    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 mt-1 w-full overflow-auto rounded-lg bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm max-h-60 {{ $dropUp ? 'bottom-full mb-1' : 'top-full mt-1' }}"
        style="display: none;"
    >
        <ul class="divide-y divide-slate-50">
            <template x-for="(label, key) in filteredOptions" :key="key">
                <li 
                    @click="select(key, label)"
                    class="relative cursor-pointer select-none py-2 pl-3 pr-9 hover:bg-teal-50 text-slate-900"
                    :class="value == key ? 'bg-teal-50 font-medium text-teal-700' : ''"
                >
                    <span class="block truncate" x-text="label"></span>
                    <span x-show="value == key" class="absolute inset-y-0 right-0 flex items-center pr-4 text-teal-600">
                        <x-lucide-check class="h-4 w-4" />
                    </span>
                </li>
            </template>
            <li x-show="Object.keys(filteredOptions).length === 0" class="relative cursor-default select-none py-3 pl-3 pr-9 text-slate-500 italic text-center text-xs">
                Tidak ada hasil.
            </li>
        </ul>
    </div>
</div>