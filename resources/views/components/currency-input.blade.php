@props([
    'name',
    'id' => null,
    'value' => null,
    'disabled' => false,
])

@php
    $id = $id ?? $name;
    $initial = $value ?? '';
@endphp

<div
    x-data="{
        value: @js((string) $initial),
        displayValue: '',

        normalizeNumber(val) {
            if (val === null || val === undefined) return '';

            let str = val.toString().trim();
            if (str === '') return '';

            str = str.replace(/,/g, '.');
            str = str.replace(/\.(?=\d{3,}(?:\.|$))/g, '');

            const num = parseFloat(str);
            if (Number.isNaN(num)) return '';

            return Math.round(num);
        },

        format(val) {
            const num = this.normalizeNumber(val);
            if (num === '') return '';

            return new Intl.NumberFormat('id-ID').format(num);
        },

        updateValue(event) {
            const input = event.target.value || '';
            const rawValue = this.normalizeNumber(input);
            this.value = rawValue === '' ? '' : rawValue.toString();
            this.displayValue = this.format(rawValue);
            if (this.$refs.hidden) {
                this.$refs.hidden.value = this.value;
            }
        },

        init() {
            this.value = this.normalizeNumber(this.value);
            this.displayValue = this.format(this.value);
            if (this.$refs.hidden) {
                this.$refs.hidden.value = this.value;
            }
        }
    }"
    class="relative text-slate-900"
>
    <input type="hidden" x-ref="hidden" name="{{ $name }}" :value="value">

    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
        <span class="text-slate-500 sm:text-sm font-medium">Rp</span>
    </div>

    <input
        type="text"
        x-model="displayValue"
        @input="updateValue"
        id="{{ $id }}"
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->merge([
            'class' => 'block w-full rounded-xl border-slate-300 pl-10 pr-3 py-[9px] text-right focus:border-teal-500 focus:ring-teal-500 text-sm shadow-sm placeholder:text-slate-400 font-mono tracking-wide'
        ]) }}
        placeholder="0"
    >
</div>
