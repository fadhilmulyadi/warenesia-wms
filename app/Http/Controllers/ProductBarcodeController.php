<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ProductBarcodeController extends Controller
{
    private const QR_SIZE = 256;
    private const QR_MARGIN = 1;

    public function show(Product $product): Response
    {
        $this->authorize('viewBarcode', $product);

        $payload = $product->getBarcodePayload();

        $image = QrCode::format('svg')
            ->size(self::QR_SIZE)
            ->margin(self::QR_MARGIN)
            ->generate($payload);

        return response($image, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'inline; filename="product-' . $product->id . '-qrcode.png"',
        ]);
    }

    public function label(Product $product): View
    {
        $this->authorize('viewBarcode', $product);

        return view('products.barcode-label', compact('product'));
    }
}
