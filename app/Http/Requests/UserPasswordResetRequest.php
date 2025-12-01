<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UserPasswordResetRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\User|null $target */
        $target = $this->route('user');

        return $this->user()?->can('update', $target ?? User::class) ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
