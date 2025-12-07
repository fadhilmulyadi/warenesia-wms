<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierRegistrationRequest;
use App\Services\SupplierService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SupplierRegistrationController extends Controller
{
    public function __construct(private SupplierService $suppliers)
    {
    }

    public function create(): View
    {
        return view('auth.supplier-register');
    }

    public function store(SupplierRegistrationRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Ensure critical fields are present
        $data['company_name'] = $data['company_name'] ?? $request->input('company_name');
        $data['supplier_name'] = $data['supplier_name'] ?? $request->input('supplier_name');
        $data['phone'] = $data['phone'] ?? $request->input('phone');

        $this->suppliers->register($data);

        return redirect()
            ->route('login')
            ->with('success', 'Supplier berhasil ditambahkan.');
    }
}
