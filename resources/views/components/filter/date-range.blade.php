@props([
    'fromName',
    'toName',
    'flagName' => 'date_range',
    'fromValue' => null,
    'toValue' => null,
    'layoutClass' => 'flex items-center gap-2',
])

@php
    $fromVal = $fromValue ?? request($fromName);
    $toVal = $toValue ?? request($toName);
@endphp

<div
    x-data="{
        from: @js($fromVal ?? ''),
        to: @js($toVal ?? ''),
        updateMeta() {
            const hasRange = !!(this.from || this.to);
            const flag = this.$refs.flag;
            const display = this.$refs.display;
            const option = this.$refs.option;

            if (flag) {
                flag.value = hasRange ? '1' : '';
            }

            if (display && option) {
                option.textContent = hasRange
                    ? [this.from || 'Dari', this.to || 'Sampai'].join(' - ')
                    : '';
                display.value = hasRange ? 'applied' : '';
                display.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    }"
    x-init="updateMeta()"
    class="space-y-2"
>
    <input type="hidden" name="{{ $flagName }}" x-ref="flag">
    <select class="hidden" x-ref="display">
        <option value=""></option>
        <option value="applied" x-ref="option"></option>
    </select>

    <div class="{{ $layoutClass }}">
        <x-form.date
            name="{{ $fromName }}"
            x-model="from"
            placeholder="Dari tanggal"
            x-on:change="updateMeta()"
        />
        <x-form.date
            name="{{ $toName }}"
            x-model="to"
            placeholder="Sampai tanggal"
            x-on:change="updateMeta()"
        />
    </div>
</div>
