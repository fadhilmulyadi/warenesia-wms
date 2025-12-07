@extends('layouts.app')

@section('title', 'Edit Restock')

@section('page-header')
    <x-page-header
        title="Edit Pesanan Restock"
        description="Ubah detail item atau kuantitas order pembelian"
    />
@endsection

@section('content')
    {{-- MOBILE VERSION --}}
    <x-mobile.form
        form-id="restock-form-mobile"
        save-label="Simpan Perubahan"
        save-icon="save"
        :show-delete="true"
        delete-action="{{ route('restocks.destroy', $restock) }}"
        delete-label="Hapus Restock"
        delete-confirm="Hapus pesanan restock ini?"
        :use-delete-modal="true"
        delete-title="Hapus Restock"
        item-name="#{{ $restock->po_number }}"
    >
        <x-slot:fields>
            @if($errors->any())
                <div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700 mb-4">
                    Terdapat kesalahan input. Periksa kembali formulir di bawah.
                </div>
            @endif

            <form
                id="restock-form-mobile"
                method="POST"
                action="{{ route('restocks.update', $restock) }}"
                class="space-y-6"
            >
                @csrf
                @method('PUT')
                
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
                                :value="old('supplier_id', $restock->supplier_id)"
                                placeholder="Pilih Supplier"
                                class="mt-1 block w-full"
                                required
                            />
                            <x-input-error :messages="$errors->get('supplier_id')" class="mt-2" />
                        </div>

                        {{-- Tanggal Order --}}
                        <div>
                            <x-input-label for="order_date_mobile" value="Tanggal Order" />
                            <input
                                type="date"
                                id="order_date_mobile"
                                name="order_date"
                                value="{{ old('order_date', $restock->order_date->format('Y-m-d')) }}"
                                class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm"
                                required
                            >
                            <x-input-error :messages="$errors->get('order_date')" class="mt-2" />
                        </div>

                        {{-- Perkiraan Tiba --}}
                        <div>
                            <x-input-label for="expected_delivery_date_mobile" value="Perkiraan Tiba (Opsional)" />
                            <input
                                type="date"
                                id="expected_delivery_date_mobile"
                                name="expected_delivery_date"
                                value="{{ old('expected_delivery_date', $restock->expected_delivery_date?->format('Y-m-d')) }}"
                                class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm"
                            >
                            <x-input-error :messages="$errors->get('expected_delivery_date')" class="mt-2" />
                        </div>

                        {{-- Catatan --}}
                        <div>
                            <x-input-label for="notes_mobile" value="Catatan" />
                            <textarea
                                id="notes_mobile"
                                name="notes"
                                rows="3"
                                class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm"
                            >{{ old('notes', $restock->notes) }}</textarea>
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
                        <x-transactions.items-table
                            :products="$products"
                            :initial-items="$restock->items->map(fn($item) => [
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

    {{-- DESKTOP VERSION --}}
    <div class="hidden md:block space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <x-breadcrumbs :items="[
                'Restock' => route('restocks.index'),
                '#'.$restock->po_number => route('restocks.show', $restock),
                'Edit' => '#',
            ]" />
            <div class="flex flex-wrap gap-2 justify-end">
                <x-action-button href="{{ route('restocks.index') }}" variant="secondary" icon="arrow-left">
                    Kembali
                </x-action-button>
                <x-action-button type="submit" form="restock-form" variant="primary" icon="save">
                    Simpan Perubahan
                </x-action-button>
            </div>
        </div>

        @if($errors->any())
            <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                Terdapat kesalahan input. Periksa kembali formulir di bawah.
            </div>
        @endif

        <form
            id="restock-form"
            method="POST"
            action="{{ route('restocks.update', $restock) }}"
            class="space-y-6"
        >
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Informasi Pesanan --}}
                <div class="lg:col-span-1">
                    <x-card class="p-5 h-full">
                        <h3 class="text-sm font-bold text-slate-800 mb-4 uppercase tracking-wider border-b border-slate-100 pb-2">
                            Informasi Pesanan
                        </h3>
                        
                        <div class="space-y-4">
                            {{-- Supplier --}}
                            <div>
                                <x-input-label for="supplier_id" value="Supplier" />
                                <x-custom-select
                                    id="supplier_id"
                                    name="supplier_id"
                                    :options="$suppliers->pluck('name', 'id')->toArray()"
                                    :value="old('supplier_id', $restock->supplier_id)"
                                    placeholder="Pilih Supplier"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <x-input-error :messages="$errors->get('supplier_id')" class="mt-2" />
                            </div>

                            {{-- Tanggal Order --}}
                            <div>
                                <x-input-label for="order_date" value="Tanggal Order" />
                                <input
                                    type="date"
                                    id="order_date"
                                    name="order_date"
                                    value="{{ old('order_date', $restock->order_date->format('Y-m-d')) }}"
                                    class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm"
                                    required
                                >
                                <x-input-error :messages="$errors->get('order_date')" class="mt-2" />
                            </div>

                            {{-- Perkiraan Tiba --}}
                            <div>
                                <x-input-label for="expected_delivery_date" value="Perkiraan Tiba (Opsional)" />
                                <input
                                    type="date"
                                    id="expected_delivery_date"
                                    name="expected_delivery_date"
                                    value="{{ old('expected_delivery_date', $restock->expected_delivery_date?->format('Y-m-d')) }}"
                                    class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm"
                                >
                                <x-input-error :messages="$errors->get('expected_delivery_date')" class="mt-2" />
                            </div>

                            {{-- Catatan --}}
                            <div>
                                <x-input-label for="notes" value="Catatan" />
                                <textarea
                                    id="notes"
                                    name="notes"
                                    rows="3"
                                    class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm"
                                >{{ old('notes', $restock->notes) }}</textarea>
                                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                            </div>
                        </div>
                    </x-card>
                </div>

                {{-- Daftar Item --}}
                <div class="lg:col-span-2">
                    <x-card class="p-5 h-full flex flex-col">
                        <h3 class="text-sm font-bold text-slate-800 mb-4 uppercase tracking-wider border-b border-slate-100 pb-2">
                            Daftar Item
                        </h3>
                        
                        <div class="flex-1">
                            <x-transactions.items-table
                                :products="$products"
                                :initial-items="$restock->items->map(fn($item) => [
                                    'product_id' => $item->product_id,
                                    'quantity' => $item->quantity,
                                    'unit_cost' => $item->unit_cost,
                                ])->toArray()"
                            />
                        </div>
                    </x-card>
                </div>
            </div>
        </form>
    </div>
    <x-confirm-delete-modal />
@endsection
