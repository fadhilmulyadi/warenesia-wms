<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileDeleteRequest extends FormRequest
{
    /**
     * Validation errors should continue to use the profile deletion bag.
     *
     * @var string
     */
    protected $errorBag = 'userDeletion';

    public function authorize(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = $this->user();

        return $user?->can('deleteProfile', $user) ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'password' => ['required', 'current_password'],
        ];
    }
}
