<?php

namespace App\Http\Requests;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserStatusUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\User|null $target */
        $target = $this->route('user');

        if ($this->isApproveRoute()) {
            return $this->user()?->can('approveSupplier', $target ?? User::class) ?? false;
        }

        return $this->user()?->can('update', $target ?? User::class) ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'required', Rule::in(UserStatus::values())],
        ];
    }

    private function isApproveRoute(): bool
    {
        return $this->routeIs('users.approve');
    }
}
