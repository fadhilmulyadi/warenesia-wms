@props(['value' => [], 'readonly' => false])

<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
    {{-- Tanggal --}}
    <div>
        <x-input-label value="Tanggal Transaksi" class="mb-1" />
        <input type="date" name="transaction_date" 
               value="{{ $value['date'] ?? now()->format('Y-m-d') }}"
               class="w-full h-[42px] rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-teal-500 focus:ring-teal-500 disabled:bg-slate-100 disabled:text-slate-500"
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
                  class="w-full rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-teal-500 focus:ring-teal-500 disabled:bg-slate-100 disabled:text-slate-500"
                  placeholder="Contoh: No. PO External, Keterangan pengiriman..."
                  {{ $readonly ? 'disabled' : '' }}>{{ $value['notes'] ?? '' }}</textarea>
    </div>
</div>
