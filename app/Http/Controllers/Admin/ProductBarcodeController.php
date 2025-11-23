<?php

namespace App\Http\Controllers\Admin;

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
        $payload = $product->getBarcodePayload();

        $image = QrCode::format('png')
            ->size(self::QR_SIZE)
            ->margin(self::QR_MARGIN)
            ->generate($payload);

        return response($image, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="product-' . $product->id . '-qrcode.png"',
        ]);
    }

    public function label(Product $product): View
    {
        return view('admin.products.barcode-label', compact('product'));
    }
}
