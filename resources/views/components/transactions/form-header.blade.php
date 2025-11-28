@props(['value' => [], 'readonly' => false])

<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
    {{-- Tanggal --}}
    <div>
        <x-input-label value="Tanggal Transaksi" class="mb-1" />
        <input type="date" name="transaction_date" 
               value="{{ $value['date'] ?? now()->format('Y-m-d') }}"
               class="w-full rounded-lg border-slate-200 text-sm disabled:bg-slate-50 disabled:text-slate-500"
               {{ $readonly ? 'disabled' : '' }} required>
    </div>

    {{-- Slot Dinamis (Supplier/Customer) --}}
    <div class="md:col-span-2">
        {{ $slot }}
    </div>

    {{-- Notes --}}
    <div class="md:col-span-3">
        <x-input-label value="Catatan / Referensi" class="mb-1" />
        <textarea name="notes" rows="2"
                  class="w-full rounded-lg border-slate-200 text-sm disabled:bg-slate-50 disabled:text-slate-500"
                  placeholder="Contoh: No. PO External, Keterangan pengiriman..."
                  {{ $readonly ? 'disabled' : '' }}>{{ $value['notes'] ?? '' }}</textarea>
    </div>
</div>