    @props([
        'name' => '',
        'options' => [],
        'value' => null,
        'placeholder' => 'Pilih opsi',
        'width' => 'w-full',
        'onChange' => null,
        'dropUp' => false,
        'prefixLabel' => null,
    ])

    @php
        $selectedLabel = collect($options)->first(function($label, $val) use ($value) {
            return (string)$val === (string)$value;
        }) ?? $placeholder;
    @endphp

    <div 
        x-data="{
            open: false,
            value: @js($value),
            label: @js($selectedLabel),
            
            select(val, lbl) {
                this.value = val;
                this.label = lbl;
                this.open = false;
                
                $refs.input.value = val;
                $refs.input.dispatchEvent(new Event('change'));

                @if($onChange)
                    {{ $onChange }}
                @endif
            }
        }"
        {{-- Container utama --}}
        class="relative {{ $width }} h-[42px] flex items-center"
        @click.outside="open = false"
    >
        {{-- Input Hidden untuk Form --}}
        <input 
            type="hidden" 
            name="{{ $name }}" 
            x-ref="input" 
            :value="value"
        >

        <button 
            type="button"
            @click="open = !open"
            class="flex items-center justify-between w-full h-full bg-white border border-slate-200 text-xs 
                rounded-xl px-1
                shadow-sm
                hover:bg-slate-50 transition-colors
                focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500"
            :class="open ? 'ring-2 ring-teal-500/20 border-teal-500' : ''"
        >
            {{-- Wrapper untuk Label + Value --}}
            <div class="flex items-center flex-1 pl-2 overflow-hidden">
                
                @if($prefixLabel)
                    <span class="mr-2 text-xs font-medium text-slate-400 whitespace-nowrap">
                        {{ $prefixLabel }}
                    </span>
                @endif
                
                {{-- Teks Value --}}
                <span x-text="label" class="truncate font-bold text-slate-700"></span>
            </div>
            
            {{-- Icon Chevron --}}
            <div class="pr-2 text-slate-400 flex-shrink-0">
                <svg 
                    class="w-3 h-3 transition-transform duration-200" 
                    :class="open ? 'rotate-180' : ''"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
        </button>

        {{-- DROPDOWN MENU --}}
        <div 
            x-show="open"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            
            class="absolute z-50 w-full bg-white border border-slate-100 rounded-lg shadow-xl max-h-60 overflow-auto 
            {{ $dropUp ? 'bottom-full mb-1' : 'top-full mt-1' }}" 
            
            style="display: none;"
        >
            <ul class="py-1 text-xs text-slate-700">
                @foreach($options as $val => $lbl)
                    <li 
                        @click="select('{{ $val }}', '{{ $lbl }}')"
                        class="px-3 py-2 cursor-pointer hover:bg-teal-50 hover:text-teal-700 transition-colors flex items-center justify-between group"
                        :class="value == '{{ $val }}' ? 'bg-teal-50 text-teal-700 font-semibold' : ''"
                    >
                        <span>{{ $lbl }}</span>
                        
                        <svg 
                            x-show="value == '{{ $val }}'"
                            class="w-3.5 h-3.5 text-teal-600" 
                            fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>