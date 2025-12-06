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

        format(val) {
            if (!val) return '';
            let number = val.toString().replace(/[^0-9]/g, '');
            return new Intl.NumberFormat('id-ID').format(number);
        },

        updateValue(event) {
            const input = event.target.value || '';
            const rawValue = input.replace(/\./g, '');
            this.value = rawValue;
            this.displayValue = this.format(rawValue);
            if (this.$refs.hidden) {
                this.$refs.hidden.value = rawValue;
            }
        },

        init() {
            if (this.value) {
                this.displayValue = this.format(this.value);
            }
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
