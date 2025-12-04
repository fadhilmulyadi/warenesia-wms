<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PasswordUpdateRequest;
use App\Services\UserManagementService;
use Illuminate\Http\RedirectResponse;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(PasswordUpdateRequest $request, UserManagementService $users): RedirectResponse
    {
        $users->resetPassword($request->user(), $request->validated()['password']);

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
