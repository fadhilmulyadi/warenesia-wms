@php
    /** @var \App\Models\Category $category */
    $category = $category ?? new \App\Models\Category();
    $readonly = $readonly ?? false;
    $initialName = old('name', $category->name);
    $initialPrefix = old('sku_prefix', $category->sku_prefix);
@endphp

<div
    x-data="{
        name: @js($initialName),
        prefix: @js($initialPrefix),
        prefixLocked: Boolean(@js($initialPrefix)),
        buildPrefix(input) {
            const slug = input
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .trim()
                .replace(/\s+/g, '-');

            if (!slug) return '';

            const words = slug.split('-').filter(Boolean);
            if (!words.length) return '';

            let draft = '';

            for (const word of words) {
                draft += word.charAt(0);
                if (draft.length >= 3) break;
            }

            if (draft.length < 3) {
                const tail = words[words.length - 1].slice(1);
                draft += tail.slice(0, Math.max(0, 3 - draft.length));
            }

            return draft.slice(0, 3).toUpperCase();
        },
        syncPrefix() {
            if (this.prefixLocked) return;
            this.prefix = this.buildPrefix(this.name);
        },
        markManual() {
            this.prefixLocked = true;
            this.prefix = (this.prefix || '').toUpperCase();
        }
    }"
    x-init="syncPrefix()"
    class="space-y-6"
>
    <div class="space-y-2">
        <x-input-label for="name" value="Nama Kategori" class="text-sm font-semibold text-slate-700" />
        <input
            type="text"
            id="name"
            name="name"
            x-model="name"
            @input="syncPrefix()"
            :readonly="@js($readonly)"
            class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500 disabled:bg-slate-100 disabled:text-slate-500"
            placeholder="Contoh: Elektronik, Peralatan Dapur"
            required
        />
        <x-input-error class="mt-1" :messages="$errors->get('name')" />
    </div>

    <div class="space-y-2">
        <div class="flex items-center justify-between">
            <x-input-label for="sku_prefix" value="SKU Prefix" class="text-sm font-semibold text-slate-700" />
            <span class="text-[11px] text-slate-500">Auto-generate & dapat diedit</span>
        </div>
        <input
            type="text"
            id="sku_prefix"
            name="sku_prefix"
            maxlength="6"
            x-model="prefix"
            @focus="markManual()"
            @input="markManual()"
            :readonly="@js($readonly)"
            class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500 uppercase tracking-[0.2em] font-semibold"
            placeholder="ELE, PDA"
            required
        />
        <x-input-error class="mt-1" :messages="$errors->get('sku_prefix')" />
    </div>

    <div class="space-y-2">
        <x-input-label for="description" value="Deskripsi" class="text-sm font-semibold text-slate-700" />
        <textarea
            id="description"
            name="description"
            rows="5"
            :readonly="@js($readonly)"
            class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500 disabled:bg-slate-100 disabled:text-slate-500"
            placeholder="Catatan singkat untuk tim gudang..."
        >{{ old('description', $category->description) }}</textarea>
        <x-input-error class="mt-1" :messages="$errors->get('description')" />
    </div>
</div>
