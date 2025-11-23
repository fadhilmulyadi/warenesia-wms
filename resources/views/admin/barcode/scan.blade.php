@extends('layouts.app')

@section('title', 'Scan Product')

@section('page-header')
    <div class="flex flex-col">
        <h1 class="text-base font-semibold text-slate-900">Scan product</h1>
        <p class="text-xs text-slate-500">
            Point your barcode/QR scanner to a product code. The scanned value will be matched against SKU.
        </p>
    </div>
@endsection

@section('content')
    @php
        $canViewProductDetail = auth()->check()
            && in_array(auth()->user()->role, ['admin', 'manager'], true);
    @endphp

    <div
        x-data="{ scanValue: @js($lastScannedCode) }"
        x-init="$nextTick(() => $refs.scanInput.focus())"
        class="max-w-3xl mx-auto space-y-4 text-xs"
    >
        <div class="rounded-2xl border border-slate-200 bg-white p-4 space-y-3">
            <form
                method="POST"
                action="{{ route('admin.barcode.scan.handle') }}"
                class="space-y-2"
            >
                @csrf

                <label class="text-[11px] text-slate-600 block">
                    Scan code (SKU) or type manually, then press Enter:
                </label>

                <input
                    x-ref="scanInput"
                    x-model="scanValue"
                    type="text"
                    name="code"
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-[13px] tracking-[0.1em]"
                    autocomplete="off"
                >

                @error('code')
                    <p class="text-[11px] text-red-600">{{ $message }}</p>
                @enderror
            </form>

            @if($lastScannedCode !== '')
                <p class="text-[11px] text-slate-500">
                    Last scanned code: <span class="font-medium text-slate-700">{{ $lastScannedCode }}</span>
                </p>
            @endif
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4">
            @if($lastScannedCode === '')
                <p class="text-[11px] text-slate-500">
                    Scan a code to see product details here.
                </p>
            @elseif($product === null)
                <p class="text-[11px] text-red-600">
                    No product found with SKU <span class="font-mono">{{ $lastScannedCode }}</span>.
                </p>
            @else
                <div class="flex flex-col md:flex-row gap-4 items-start">
                    <div class="flex-1 space-y-1">
                        <div class="text-[11px] font-semibold text-slate-600 uppercase">
                            Matched product
                        </div>
                        <div class="text-sm font-semibold text-slate-900">
                            {{ $product->name }}
                        </div>
                        <div class="text-[11px] text-slate-500">
                            SKU: <span class="font-mono">{{ $product->sku }}</span>
                        </div>
                        <div class="text-[11px] text-slate-500">
                            Current stock:
                            <span class="font-semibold text-slate-800">{{ $product->current_stock }}</span>
                            {{ $product->unit }}
                        </div>
                        @if($product->category)
                            <div class="text-[11px] text-slate-500">
                                Category: {{ $product->category->name }}
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-col items-center gap-2">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <img
                                src="{{ route('admin.products.barcode', $product) }}"
                                alt="QR code for {{ $product->name }}"
                                class="h-24 w-24 object-contain"
                            >
                        </div>
                        <div class="flex flex-col gap-1 w-full">
                            @if($canViewProductDetail)
                                <a
                                    href="{{ route('admin.products.show', $product) }}"
                                    class="inline-flex items-center justify-center rounded-lg border border-slate-200 px-3 py-1.5 text-[11px] text-slate-700 hover:bg-slate-50"
                                >
                                    <x-lucide-eye class="h-3 w-3 mr-1" />
                                    View product detail
                                </a>
                            @endif
                            <a
                                href="{{ route('admin.purchases.create', ['product_id' => $product->id]) }}"
                                class="inline-flex items-center justify-center rounded-lg border border-teal-200 bg-teal-50 px-3 py-1.5 text-[11px] text-teal-800 hover:bg-teal-100"
                            >
                                <x-lucide-arrow-down-circle class="h-3 w-3 mr-1" />
                                New incoming transaction with this product
                            </a>
                            <a
                                href="{{ route('admin.sales.create', ['product_id' => $product->id]) }}"
                                class="inline-flex items-center justify-center rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-[11px] text-amber-800 hover:bg-amber-100"
                            >
                                <x-lucide-arrow-up-circle class="h-3 w-3 mr-1" />
                                New outgoing transaction with this product
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
