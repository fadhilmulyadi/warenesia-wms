<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Barcode Label - {{ $product->name }}</title>
    @vite('resources/css/app.css')

    <style>
        @media print {
            @page {
                size: auto;
                margin: 8mm;
            }

            body {
                margin: 0;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body class="bg-white text-slate-900 flex items-center justify-center min-h-screen">
    <div class="space-y-4">
        <div class="no-print flex justify-end">
            <button
                type="button"
                onclick="window.print()"
                class="inline-flex items-center rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50"
            >
                Print
            </button>
        </div>

        <div class="border border-slate-300 rounded-xl p-4 w-64 flex flex-col items-center gap-2">
            <div class="text-xs font-semibold text-slate-700 text-center">
                {{ $product->name }}
            </div>
            <div class="text-[11px] text-slate-500 text-center">
                SKU: {{ $product->sku }}
            </div>

            <div class="mt-2">
                {{-- Inline QR as SVG for better print quality --}}
                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(160)->margin(1)->generate($product->getBarcodePayload()) !!}
            </div>

            <div class="mt-2 text-[11px] text-slate-600 text-center">
                {{ $product->getBarcodeLabel() }}
            </div>
        </div>
    </div>
</body>
</html>
