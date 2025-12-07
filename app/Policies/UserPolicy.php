<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function view(User $user, User $model): bool
    {
        return $this->isAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, User $model): bool
    {
        if (!$this->isAdmin($user)) {
            return false;
        }

        return true;
    }

    public function updateProfile(User $user, User $model): bool
    {
        return (int) $user->id === (int) $model->id;
    }

    public function delete(User $user, User $model): bool
    {
        return false;
    }

    public function approveSupplier(User $user, User $model): bool
    {
        return $this->isAdmin($user);
    }

    public function deleteProfile(User $user, User $model): bool
    {
        if ((int) $user->id !== (int) $model->id) {
            return false;
        }

        if ($model->isSuperAdmin()) {
            return false;
        }

        if ($model->role === Role::ADMIN->value && !$this->hasAnotherActiveAdmin($model)) {
            return false;
        }

        return true;
    }

    private function isAdmin(User $user): bool
    {
        return $user->role === Role::ADMIN->value;
    }

    private function hasAnotherActiveAdmin(User $except): bool
    {
        return User::query()
            ->where('role', Role::ADMIN->value)
            ->whereNull('deleted_at')
            ->where('id', '!=', $except->id)
            ->exists();
    }
}
