<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductScanController extends Controller
{
    public function showForm(Request $request): View
    {
        $this->authorize('scanBarcode', Product::class);

        $lastScannedCode = trim((string) $request->query('code', ''));
        $product = null;

        if ($lastScannedCode !== '') {
            $product = Product::query()
                ->with('category')
                ->where('sku', $lastScannedCode)
                ->first();
        }

        return view('barcode.scan', compact('lastScannedCode', 'product'));
    }

    public function handleScan(Request $request): RedirectResponse
    {
        $this->authorize('scanBarcode', Product::class);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255'],
        ]);

        $code = trim($validated['code']);

        if ($code === '') {
            return back()
                ->withInput()
                ->withErrors(['code' => 'Code cannot be empty.']);
        }

        return redirect()->route('barcode.scan', ['code' => $code]);
    }
}
