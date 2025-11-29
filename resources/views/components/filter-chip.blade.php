@props([
    'label',
    'name',
])

<div
    x-data="{
        open: false,
        isActive: false,
        labelText: @js($label),
        defaultLabel: @js($label),
        name: @js($name),

        init() {
            this.updateLabel();
            this.bindInputs();
        },

        bindInputs() {
            const inputs = $el.querySelectorAll('input, select');
            inputs.forEach(input => {
                input.addEventListener('change', () => this.updateLabel());
            });
        },

        resetInputs() {
            $el.querySelectorAll('input[type=checkbox]').forEach(el => el.checked = false);
            $el.querySelectorAll('select').forEach(el => el.selectedIndex = -1);
        },

        updateLabel() {
            let labels = [];
            
            // Handle Select Options
            const selects = $el.querySelectorAll('select');
            selects.forEach(select => {
                Array.from(select.selectedOptions).forEach(opt => {
                    if(opt.value) labels.push(opt.text.trim());
                });
            });

            // Handle Checkboxes
            const checkboxes = $el.querySelectorAll('input[type=checkbox]:checked');
            checkboxes.forEach(chk => {
                const text = chk.closest('label')?.innerText || chk.value;
                if(text) labels.push(text.trim());
            });

            if (labels.length > 0) {
                this.isActive = true;
                const prefix = this.defaultLabel + ': ';
                if (labels.length <= 2) {
                    this.labelText = prefix + labels.join(', ');
                } else {
                    this.labelText = prefix + labels.slice(0, 2).join(', ') + ', +' + (labels.length - 2);
                }
            } else {
                this.isActive = false;
                this.labelText = this.defaultLabel;
            }

            this.$dispatch('filter-chip-activity', { name: this.name, active: this.isActive });
        },

        clearFilter() {
            this.resetInputs();
            this.updateLabel();
            this.open = false;
            $el.closest('form').submit();
        }
    }"
    class="relative"
    @click.outside="open = false"
>
    {{-- TOMBOL FILTER --}}
    <button 
        type="button"
        @click="open = !open"
        class="inline-flex items-center justify-center h-9 px-4 rounded-lg text-xs font-semibold border transition-all duration-200"
        :class="isActive 
            ? 'bg-teal-50 border-teal-200 text-teal-700' 
            : 'bg-white border-slate-300 text-slate-600 hover:bg-slate-50'"
    >
        <span x-text="labelText"></span>
        <x-lucide-chevron-down class="w-3.5 h-3.5 ml-1 transition-transform" x-bind:class="open ? 'rotate-180' : ''" x-show="!isActive" />
        
        {{-- Tombol X (Hanya muncul jika aktif) --}}
        <div 
            x-show="isActive" 
            @click.stop="clearFilter()"
            class="hover:bg-teal-100 p-0.5 rounded-md cursor-pointer transition-colors ml-1"
        >
            <x-lucide-x class="w-3.5 h-3.5" />
        </div>
    </button>

    {{-- DROPDOWN CONTENT (Popover) --}}
    <div 
        x-show="open" 
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-30 mt-2 w-64 origin-top-left rounded-xl bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none p-3"
        style="display: none;"
    >
        <div class="mb-2 pb-2 border-b border-slate-100">
            <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Filter {{ $label }}</span>
        </div>

        {{-- Isi Slot (Form inputs) --}}
        <div class="max-h-60 overflow-y-auto space-y-1">
            {{ $slot }}
        </div>

        {{-- Footer Actions --}}
        <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between gap-2">
            <button 
                type="button"
                @click="clearFilter()"
                class="text-xs font-semibold text-red-500 hover:text-red-600 hover:underline"
            >
                Clear
            </button>
            <button 
                type="submit"
                class="bg-teal-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-teal-700 w-full"
            >
                Terapkan
            </button>
        </div>
    </div>
</div>
