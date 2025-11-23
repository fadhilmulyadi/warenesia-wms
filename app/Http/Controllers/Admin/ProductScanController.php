<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductScanController extends Controller
{
    public function showForm(Request $request): View
    {
        $lastScannedCode = trim((string) $request->query('code', ''));
        $product = null;

        if ($lastScannedCode !== '') {
            $product = Product::query()
                ->with('category')
                ->where('sku', $lastScannedCode)
                ->first();
        }

        return view('admin.barcode.scan', compact('lastScannedCode', 'product'));
    }

    public function handleScan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255'],
        ]);

        $code = trim($validated['code']);

        if ($code === '') {
            return back()
                ->withInput()
                ->withErrors(['code' => 'Code cannot be empty.']);
        }

        return redirect()->route('admin.barcode.scan', ['code' => $code]);
    }
}
