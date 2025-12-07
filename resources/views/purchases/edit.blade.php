@extends('layouts.app')

@section('title', 'Edit Pembelian')

@section('page-header')
    {{-- PAGE HEADER: Desktop --}}
    <div class="hidden md:block">
        <x-page-header
            title="Edit Barang Masuk"
            :description="'Koreksi data penerimaan untuk Ref #' . $purchase->transaction_number"
        />
    </div>
    {{-- PAGE HEADER: Mobile --}}
    <div class="md:hidden">
        <x-mobile-header
            title="Edit Pembelian"
            back="{{ route('transactions.index', ['tab' => 'purchases']) }}"
        />
    </div>
@endsection

@section('content')
    {{-- MOBILE FORM --}}
    <x-mobile.form
        form-id="edit-form-mobile"
        save-label="Simpan Perubahan"
        save-icon="save"
        :show-delete="true"
        delete-action="{{ route('purchases.destroy', $purchase) }}"
        delete-label="Hapus Pembelian"
        delete-confirm="Hapus pembelian ini?"
        :use-delete-modal="true"
        delete-title="Hapus Pembelian"
        item-name="#{{ $purchase->transaction_number }}"
    >
        <x-slot:fields>
            @if($errors->any())
                <div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700 mb-4">
                    Terdapat kesalahan input. Periksa kembali formulir di bawah.
                </div>
            @endif

            <form
                id="edit-form-mobile"
                method="POST"
                action="{{ route('purchases.update', $purchase) }}"
                class="space-y-6"
            >
                @csrf
                @method('PUT')
                
                {{-- SECTION: Transaction Info --}}
                <x-card class="p-4 space-y-4">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2">
                        Informasi Transaksi
                    </h3>

                    <div class="space-y-4">
                        {{-- Date --}}
                        <div>
                            <x-input-label for="transaction_date_mobile" value="Tanggal Transaksi" />
                            <input
                                type="date"
                                id="transaction_date_mobile"
                                name="transaction_date"
                                value="{{ old('transaction_date', $purchase->transaction_date->format('Y-m-d')) }}"
                                class="mt-1 block w-full h-[42px] rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-teal-500 focus:ring-teal-500 sm:text-sm"
                                required
                            >
                            <x-input-error :messages="$errors->get('transaction_date')" class="mt-2" />
                        </div>

                        {{-- Supplier --}}
                        <div>
                            <x-input-label for="supplier_id_mobile" value="Supplier" />
                            @if($purchase->restock_order_id)
                                <input type="hidden" name="supplier_id" value="{{ $purchase->supplier_id }}">
                                <div class="mt-1 block w-full rounded-lg border-slate-200 bg-slate-50 text-slate-500 sm:text-sm px-3 py-2 border">
                                    {{ $purchase->supplier->name ?? 'Unknown Supplier' }}
                                </div>
                            @else
                                <x-custom-select
                                    id="supplier_id_mobile"
                                    name="supplier_id"
                                    :options="$suppliers->pluck('name', 'id')->toArray()"
                                    :value="old('supplier_id', $purchase->supplier_id)"
                                    placeholder="Pilih Supplier"
                                    class="mt-1 block w-full"
                                    required
                                />
                            @endif
                            <x-input-error :messages="$errors->get('supplier_id')" class="mt-2" />
                        </div>

                        {{-- Notes --}}
                        <div>
                            <x-input-label for="notes_mobile" value="Catatan / Referensi" />
                            <textarea
                                id="notes_mobile"
                                name="notes"
                                rows="2"
                                class="mt-1 block w-full rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-teal-500 focus:ring-teal-500 sm:text-sm"
                            >{{ old('notes', $purchase->notes) }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>
                    </div>
                </x-card>

                {{-- SECTION: Items --}}
                <x-card class="p-4 space-y-4">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2">
                        Daftar Item
                    </h3>
                    
                    <div class="overflow-x-auto -mx-4 px-4">
                        <x-transactions.items-table
                            :products="$products"
                            :initial-items="$purchase->items->map(fn($item) => [
                                'product_id' => $item->product_id,
                                'quantity' => $item->quantity,
                                'unit_cost' => $item->unit_cost,
                            ])->toArray()"
                        />
                    </div>
                </x-card>
            </form>
        </x-slot:fields>
    </x-mobile.form>

    {{-- PAGE CONTENT --}}
    <div class="hidden md:block space-y-6">
        {{-- TOOLBAR --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <x-breadcrumbs :items="[
                'Transaksi' => route('transactions.index'),
                'Barang Masuk' => route('transactions.index', ['tab' => 'incoming']),
                '#'.$purchase->transaction_number => route('purchases.show', $purchase),
                'Edit' => '#',
            ]" />
            <div class="flex flex-wrap gap-2 justify-end">
                <x-action-button href="{{ route('transactions.index', ['tab' => 'incoming']) }}" variant="secondary" icon="arrow-left">
                    Batal
                </x-action-button>
                <x-action-button type="submit" form="edit-form" variant="primary" icon="save">
                    Simpan Perubahan
                </x-action-button>
            </div>
        </div>

        {{-- FORM --}}
        <form
            id="edit-form"
            method="POST"
            action="{{ route('purchases.update', $purchase) }}"
            class="space-y-6"
        >
            @csrf
            @method('PUT')
            
            <x-card class="p-6">
                <x-transactions.form-header
                    :value="['date' => $purchase->transaction_date->format('Y-m-d'), 'notes' => $purchase->notes]"
                >
                    <x-input-label value="Supplier" class="mb-1" />
                    @if($purchase->restock_order_id)
                        <input type="hidden" name="supplier_id" value="{{ $purchase->supplier_id }}">
                        <div class="block w-full rounded-lg border-slate-200 bg-slate-50 text-slate-500 sm:text-sm px-3 py-2 border">
                            {{ $purchase->supplier->name ?? 'Unknown Supplier' }}
                        </div>
                    @else
                        <x-custom-select
                            name="supplier_id"
                            :options="$suppliers->pluck('name', 'id')->toArray()"
                            :value="old('supplier_id', $purchase->supplier_id)"
                            placeholder="Pilih Supplier"
                            required
                        />
                    @endif
                </x-transactions.form-header>

                <div class="mt-8">
                    <h3 class="text-base font-semibold text-slate-900 mb-4">Daftar Item</h3>
                    <x-transactions.items-table
                        :products="$products"
                        :initial-items="$purchase->items->map(fn($item) => [
                            'product_id' => $item->product_id,
                            'quantity' => $item->quantity,
                            'unit_cost' => $item->unit_cost,
                        ])->toArray()"
                    />
                </div>
            </x-card>
        </form>
    </div>
    <x-confirm-delete-modal />
@endsection
