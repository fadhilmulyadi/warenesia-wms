<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): RedirectResponse
    {
        return redirect()->route('supplier.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('supplier.register');
    }
}
