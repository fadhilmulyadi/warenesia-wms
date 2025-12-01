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
        $this->suppliers->register($request->validated());

        return redirect()
            ->route('login')
            ->with('status', 'Pendaftaran supplier berhasil, menunggu persetujuan admin.');
    }
}
