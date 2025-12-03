@props([
    'name' => '',
    'dynamic_name' => null,
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
    if ($options instanceof \Illuminate\Support\Collection) {
        $options = $options->toArray();
    } elseif (!is_array($options)) {
        $options = (array) $options;
    }

    $id = $id ?? $name;
    $currentValue = (string) $value;
    $initialOption = $options[$currentValue] ?? null;
    $initialLabel = is_array($initialOption) ? ($initialOption['label'] ?? '') : (string) $initialOption;
    $inputName = $dynamic_name ?? $name;
@endphp

    <div
        x-data="{
            open: false,
            value: @js($currentValue),
            inputName: @js($inputName),
        search: @js($initialLabel),
        options: @js($options),
        searchable: @js($searchable),
        dropUp: @js($dropUp),
        dropdownStyle: { top: '0px', left: '0px', width: '0px' },
        
        normalizeOption(option) {
            if (typeof option === 'object' && option !== null) {
                return {
                    label: option.label ?? option.name ?? '',
                    image: option.image ?? null,
                    prefix: option.prefix ?? option.prefixLabel ?? null,
                };
            }

            return { label: String(option), image: null, prefix: null };
        },
        normalizeOptions(rawOptions) {
            const normalized = {};

            Object.keys(rawOptions || {}).forEach(key => {
                normalized[key] = this.normalizeOption(rawOptions[key]);
            });

            return normalized;
        },
        optionLabel(option) {
            return this.normalizeOption(option).label;
        },
        
        setInputNameFromAttr() {
            const attr = this.$el.getAttribute('data-name') || this.$el.getAttribute('dynamic_name');
            if (attr) {
                this.inputName = attr;
            }
        },

        init() {
            this.options = this.normalizeOptions(this.options);

            if (this.value && this.options[this.value]) {
                this.search = this.optionLabel(this.options[this.value]);
            }

            this.setInputNameFromAttr();
            this.$nextTick(() => this.setInputNameFromAttr());

            this.$watch('value', (val) => {
                if (val && this.options[val]) {
                    this.search = this.optionLabel(this.options[val]);
                } else {
                    this.search = '';
                }
            });

            window.addEventListener('scroll', (e) => {
                if (!this.open) return;

                const dropdownRect = this.$refs.dropdown?.getBoundingClientRect();

                if (dropdownRect) {
                    const within =
                        e.target === this.$refs.dropdown ||
                        this.$refs.dropdown.contains(e.target);

                    if (within) return;
                }

                this.close();
            }, true);
        },

        get filteredOptions() {
            const normalized = this.normalizeOptions(this.options);

            if (!this.searchable) return normalized;
            if (this.search === '') return normalized;
            if (this.value && this.optionLabel(normalized[this.value]) === this.search) return normalized;

            const term = this.search.toLowerCase();
            const result = {};
            
            Object.keys(normalized).forEach(key => {
                const label = this.optionLabel(normalized[key]);
                if (label.toLowerCase().includes(term)) {
                    result[key] = normalized[key];
                }
            });
            
            return result;
        },

        calculatePosition() {
            let rect = this.$el.getBoundingClientRect();
            
            this.dropdownStyle.width = rect.width + 'px';
            this.dropdownStyle.left = rect.left + 'px';

            if (this.dropUp) {
                this.dropdownStyle.top = 'auto';
                this.dropdownStyle.bottom = (window.innerHeight - rect.top + 4) + 'px'; 
            } else {
                this.dropdownStyle.bottom = 'auto';
                this.dropdownStyle.top = (rect.bottom + 4) + 'px';
            }
        },

        select(key, option) {
            this.value = key;
            this.search = this.optionLabel(option);
            this.open = false;
            
            this.$dispatch('input', key);
            window.dispatchEvent(new CustomEvent('product-selected', {
                detail: key
            }));

            @if($onChange)
                {!! $onChange !!}
            @endif
        },

        close() {
            this.open = false;
        },
        
        toggle() {
        
            if (@js($disabled)) return;
                
            if (!this.open) {
                this.calculatePosition();
                this.setInputNameFromAttr();
                
                window.dispatchEvent(new CustomEvent('custom-select-opened', {
                    detail: this.inputName
                }));
            }
            
            this.open = !this.open;
            
            if(this.open && this.searchable) {
                $nextTick(() => $refs.searchInput.focus());
            }
        }
    }"
    x-modelable="value"
    data-name="{{ $inputName }}"
    {{ $attributes->merge(['class' => "relative {$width} h-[42px]"]) }}
    x-ref="container"
    @click.outside="close()"
    wire:ignore.self
>
    <input 
        type="hidden" 
        name="{{ $inputName }}"
        x-bind:name="inputName"
        x-model="value"
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
                'pl-[4.5rem]': '{{ $prefixLabel }}'
            }"
            autocomplete="off"
        >
        
        <div class="absolute inset-y-0 right-0 flex items-center px-2 text-slate-400 pointer-events-none">
            <x-lucide-chevron-down class="w-4 h-4 transition-transform duration-200" ::class="open ? 'rotate-180' : ''" />
        </div>
    </div>

    <template x-teleport="body">
        <div 
            x-show="open"
            x-ref="dropdown"
            :style="dropdownStyle"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="fixed z-[9999] overflow-auto rounded-lg bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm max-h-60"
            style="display: none;"
            @click.outside="close()"
        >
            <ul class="divide-y divide-slate-50">
                <template x-for="(option, key) in filteredOptions" :key="key">
                    <li 
                        @click="select(key, option)"
                        class="relative cursor-pointer select-none py-2 pl-3 pr-9 hover:bg-teal-50 text-slate-900"
                        :class="value == key ? 'bg-teal-50 font-medium text-teal-700' : ''"
                    >
                        <div class="flex items-center gap-3">
                            <div x-show="option.image" class="h-8 w-8 rounded-md overflow-hidden border border-slate-200 bg-slate-50">
                                <img :src="option.image" alt="" class="h-full w-full object-cover">
                            </div>
                            <div class="flex flex-col">
                                <span class="block truncate" x-text="option.label"></span>
                            </div>
                        </div>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 gap-2 text-teal-600">
                            <span x-show="option.prefix" class="px-2 py-0.5 text-[10px] font-semibold rounded bg-slate-100 text-slate-700 tracking-[0.15em]" x-text="option.prefix"></span>
                            <x-lucide-check x-show="value == key" class="h-4 h-4" />
                        </div>
                    </li>
                </template>
                <li x-show="Object.keys(filteredOptions).length === 0" class="relative cursor-default select-none py-3 pl-3 pr-9 text-slate-500 italic text-center text-xs">
                    Tidak ada hasil.
                </li>
            </ul>
        </div>
    </template>
</div>