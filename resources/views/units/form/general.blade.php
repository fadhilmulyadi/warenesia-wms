@php
    /** @var \App\Models\Unit $unit */
    $unit = $unit ?? new \App\Models\Unit();
@endphp

<div class="space-y-4">
    <div class="space-y-1">
        <x-input-label for="name" value="Nama Satuan" class="text-sm font-semibold text-slate-700" />
        <input
            type="text"
            id="name"
            name="name"
            value="{{ old('name', $unit->name) }}"
            required
            class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500"
            placeholder="Contoh: Pcs, Box, Liter"
        />
        <x-input-error class="mt-1" :messages="$errors->get('name')" />
    </div>

    <div class="space-y-1">
        <x-input-label for="description" value="Deskripsi" class="text-sm font-semibold text-slate-700" />
        <textarea
            id="description"
            name="description"
            rows="3"
            class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500"
            placeholder="Opsional, contoh: Konversi atau catatan penggunaan"
        >{{ old('description', $unit->description) }}</textarea>
        <x-input-error class="mt-1" :messages="$errors->get('description')" />
    </div>
</div>
