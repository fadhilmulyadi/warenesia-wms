@extends('layouts.app')

@section('title', 'Buat Pesanan Restock')

@section('page-header')
    <x-page-header title="Buat Pesanan Restock" description="Ajukan permintaan stok baru kepada supplier" />
@endsection

@section('content')
    {{-- MOBILE VERSION --}}
    <x-mobile.form form-id="restock-form-mobile" save-label="Simpan Restock" save-icon="save">
        <x-slot:fields>
            @if($errors->any())
                <div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700 mb-4">
                    <p class="font-bold">Terdapat kesalahan input:</p>
                    <ul class="list-disc list-inside mt-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="restock-form-mobile" method="POST" action="{{ route('restocks.store') }}" class="space-y-6">
                @csrf

                {{-- Informasi Pesanan --}}
                <x-card class="p-4 space-y-4">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2">
                        Informasi Pesanan
                    </h3>

                    <div class="space-y-4">
                        {{-- Supplier --}}
                        <div>
                            <x-input-label for="supplier_id_mobile" value="Supplier" />
                            <x-custom-select
                                id="supplier_id_mobile"
                                name="supplier_id"
                                :options="$suppliers->pluck('name', 'id')->toArray()"
                                :value="old('supplier_id', $prefilledSupplierId)"
                                placeholder="Pilih Supplier"
                                class="mt-1 block w-full"
                                required
                            />
                            <x-input-error :messages="$errors->get('supplier_id')" class="mt-2" />
                        </div>

                        {{-- Tanggal Order --}}
                        <div>
                            <x-input-label for="order_date_mobile" value="Tanggal Order" />
                            <input type="date" id="order_date_mobile" name="order_date" value="{{ old('order_date', $orderDate) }}"
                                class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm"
                                required>
                            <x-input-error :messages="$errors->get('order_date')" class="mt-2" />
                        </div>

                        {{-- Perkiraan Tiba --}}
                        <div>
                            <x-input-label for="expected_delivery_date_mobile" value="Perkiraan Tiba" />
                            <input type="date" id="expected_delivery_date_mobile" name="expected_delivery_date"
                                value="{{ old('expected_delivery_date', $expectedDeliveryDate) }}"
                                class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm"
                                required>
                            <x-input-error :messages="$errors->get('expected_delivery_date')" class="mt-2" />
                        </div>

                        {{-- Catatan --}}
                        <div>
                            <x-input-label for="notes_mobile" value="Catatan" />
                            <textarea id="notes_mobile" name="notes" rows="3"
                                class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm"
                                required>{{ old('notes') }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>
                    </div>
                </x-card>

                {{-- Daftar Item --}}
                <x-card class="p-4 space-y-4">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2">
                        Daftar Item
                    </h3>

                    <div class="overflow-x-auto -mx-4 px-4">
                        <x-transactions.items-table :products="$products" :initial-items="old('items', $initialItems)" priceField="unit_cost" priceLabel="Harga Beli" />
                    </div>
                </x-card>
            </form>
        </x-slot:fields>
    </x-mobile.form>

{{-- DESKTOP VERSION --}}
<div class="hidden md:block space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <x-breadcrumbs :items="['Restock' => route('restocks.index'), 'Buat Baru' => '#']" />
        <div class="flex flex-wrap gap-2 justify-end">
            <x-action-button href="{{ route('restocks.index') }}" variant="secondary" icon="arrow-left">
                Kembali
            </x-action-button>
            <x-action-button type="submit" form="restock-form" variant="primary" icon="save">
                Simpan Data
            </x-action-button>
        </div>
    </div>

    @if($errors->any())
        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <p class="font-bold">Terdapat kesalahan input:</p>
            <ul class="list-disc list-inside mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="restock-form" method="POST" action="{{ route('restocks.store') }}" class="space-y-6">
        @csrf

        <x-card class="p-6">
            {{-- Header Inputs --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-6">
                {{-- Tanggal Order --}}
                <div>
                    <x-input-label for="order_date" value="Tanggal Order" class="mb-1" />
                    <input type="date" id="order_date" name="order_date" value="{{ old('order_date', $orderDate) }}"
                        class="w-full rounded-xl h-[42px] border-slate-200 text-sm focus:border-teal-500 focus:ring-teal-500"
                        required>
                    <x-input-error :messages="$errors->get('order_date')" class="mt-2" />
                </div>

                {{-- Perkiraan Tiba --}}
                <div>
                    <x-input-label for="expected_delivery_date" value="Perkiraan Tiba" class="mb-1" />
                    <input type="date" id="expected_delivery_date" name="expected_delivery_date"
                        value="{{ old('expected_delivery_date', $expectedDeliveryDate) }}"
                        class="w-full rounded-xl h-[42px] border-slate-200 text-sm focus:border-teal-500 focus:ring-teal-500"
                        required>
                    <x-input-error :messages="$errors->get('expected_delivery_date')" class="mt-2" />
                </div>

                {{-- Supplier --}}
                <div class="md:col-span-2">
                    <x-input-label for="supplier_id" value="Supplier" class="mb-1" />
                    <x-custom-select id="supplier_id" name="supplier_id" :options="$suppliers->pluck('name', 'id')->toArray()"
                        :value="old('supplier_id', $prefilledSupplierId)"
                        placeholder="Pilih Supplier" class="block w-full" required />
                    <x-input-error :messages="$errors->get('supplier_id')" class="mt-2" />
                </div>

                {{-- Catatan --}}
                <div class="md:col-span-4">
                    <x-input-label for="notes" value="Catatan" class="mb-1" />
                    <textarea id="notes" name="notes" rows="2"
                        class="w-full rounded-xl border-slate-200 text-sm focus:border-teal-500 focus:ring-teal-500"
                        placeholder="Contoh: Keterangan tambahan..." required>{{ old('notes') }}</textarea>
                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                </div>
            </div>

            {{-- Daftar Item --}}
            <div class="mt-8">
                <h3 class="text-base font-semibold text-slate-900 mb-4">Daftar Item</h3>
                <x-transactions.items-table :products="$products" :initial-items="old('items', $initialItems)" priceField="unit_cost" priceLabel="Harga Beli" />
            </div>
        </x-card>
    </form>
</div>
@endsection
