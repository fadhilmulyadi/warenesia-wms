@extends('layouts.app')

@section('title', 'New Restock Order')

@section('page-header')
    <div class="flex flex-col">
        <h1 class="text-base font-semibold text-slate-900">New restock order</h1>
        <p class="text-xs text-slate-500">
            Create a purchase order for suppliers. Stock will be updated when items are received.
        </p>
    </div>

    <div class="flex items-center gap-2">
        <a
            href="{{ route('admin.restocks.index') }}"
            class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50"
        >
            Back to list
        </a>
        <button
            type="submit"
            form="restock-form"
            class="inline-flex items-center rounded-lg bg-teal-500 px-4 py-1.5 text-xs font-semibold text-white hover:bg-teal-600"
        >
            Save
        </button>
    </div>
@endsection

@section('content')
    @php
        $initialItems = old('items', [
            [
                'product_id' => null,
                'quantity' => 1,
                'unit_cost' => null,
            ],
        ]);
    @endphp

    <div
        x-data="{
            items: @js($initialItems),

            addItem() {
                this.items.push({ product_id: null, quantity: 1, unit_cost: null });
            },

            removeItem(index) {
                if (this.items.length > 1) {
                    this.items.splice(index, 1);
                }
            }
        }"
        class="max-w-5xl mx-auto space-y-4 text-xs"
    >
        @if($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-red-700">
                <div class="font-semibold mb-1">There are some issues with your input:</div>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $errorMessage)
                        <li>{{ $errorMessage }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white p-4 space-y-4">
            <form
                id="restock-form"
                method="POST"
                action="{{ route('admin.restocks.store') }}"
            >
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="space-y-1">
                        <label class="text-[11px] text-slate-600 block">Order date *</label>
                        <input
                            type="date"
                            name="order_date"
                            value="{{ old('order_date', $today) }}"
                            required
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-[11px]"
                        >
                    </div>

                    <div class="space-y-1">
                        <label class="text-[11px] text-slate-600 block">Expected delivery date</label>
                        <input
                            type="date"
                            name="expected_delivery_date"
                            value="{{ old('expected_delivery_date') }}"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-[11px]"
                        >
                    </div>

                    <div class="space-y-1">
                        <label class="text-[11px] text-slate-600 block">Supplier *</label>
                        <select
                            name="supplier_id"
                            required
                            class="w-full rounded-lg border border-slate-200 px-2 py-2 text-[11px]"
                        >
                            <option value="">-- Select supplier --</option>
                            @foreach($suppliers as $supplier)
                                <option
                                    value="{{ $supplier->id }}"
                                    @selected((int) old('supplier_id') === $supplier->id)
                                >
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="space-y-1 mt-4">
                    <label class="text-[11px] text-slate-600 block">Notes</label>
                    <textarea
                        name="notes"
                        rows="2"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-[11px]"
                        placeholder="Optional notes about this restock order."
                    >{{ old('notes') }}</textarea>
                </div>

                <div class="mt-6">
                    <div class="flex items-center justify-between mb-2">
                        <h2 class="text-[11px] font-semibold text-slate-800 uppercase tracking-wide">
                            Products
                        </h2>
                        <button
                            type="button"
                            @click="addItem()"
                            class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-[11px] text-slate-700 hover:bg-slate-50"
                        >
                            <x-lucide-plus class="h-3 w-3 mr-1" />
                            Add product
                        </button>
                    </div>

                    <div class="rounded-xl border border-slate-200 overflow-hidden">
                        <table class="min-w-full text-left text-xs">
                            <thead class="bg-slate-50 text-[11px] text-slate-500 uppercase tracking-wide">
                                <tr>
                                    <th class="px-3 py-2 w-1/2">Product</th>
                                    <th class="px-3 py-2 w-20 text-right">Qty</th>
                                    <th class="px-3 py-2 w-32 text-right">Unit cost (Rp)</th>
                                    <th class="px-3 py-2 w-32 text-right">Line total</th>
                                    <th class="px-3 py-2 w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="(item, index) in items" :key="index">
                                    <tr>
                                        <td class="px-3 py-2">
                                            <select
                                                class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-[11px]"
                                                :name="`items[${index}][product_id]`"
                                                x-model="item.product_id"
                                                required
                                            >
                                                <option value="">-- Select product --</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}">
                                                        {{ $product->name }} ({{ $product->sku }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <input
                                                type="number"
                                                min="1"
                                                class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-[11px] text-right"
                                                :name="`items[${index}][quantity]`"
                                                x-model.number="item.quantity"
                                                required
                                            >
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <input
                                                type="number"
                                                min="0"
                                                step="0.01"
                                                class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-[11px] text-right"
                                                :name="`items[${index}][unit_cost]`"
                                                x-model.number="item.unit_cost"
                                            >
                                        </td>
                                        <td class="px-3 py-2 text-right text-[11px] text-slate-700">
                                            <span
                                                x-text="(Number(item.quantity || 0) * Number(item.unit_cost || 0)).toLocaleString('id-ID')"
                                            ></span>
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <button
                                                type="button"
                                                @click="removeItem(index)"
                                                class="inline-flex items-center justify-center rounded-full border border-red-200 p-1 text-red-600 hover:bg-red-50"
                                            >
                                                <x-lucide-x class="h-3 w-3" />
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    @error('items')
                        <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </form>
        </div>
    </div>
@endsection
