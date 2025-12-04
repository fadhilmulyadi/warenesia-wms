<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization;

    private const VIEW_ROLES = ['admin', 'manager'];
    private const MANAGE_ROLES = ['admin', 'manager'];

    public function viewAny(User $user): bool
    {
        return $this->canView($user);
    }

    public function view(User $user, Product $product): bool
    {
        return $this->canView($user);
    }

    public function create(User $user): bool
    {
        return $this->canManage($user);
    }

    public function update(User $user, Product $product): bool
    {
        return $this->canManage($user);
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->canManage($user);
    }

    public function export(User $user): bool
    {
        return $this->canView($user);
    }

    public function viewBarcode(User $user, Product $product): bool
    {
        return $this->canView($user);
    }

    public function scanBarcode(User $user): bool
    {
        return $this->canView($user);
    }

    private function canView(User $user): bool
    {
        return in_array($user->role, self::VIEW_ROLES, true);
    }

    private function canManage(User $user): bool
    {
        return in_array($user->role, self::MANAGE_ROLES, true);
    }
}