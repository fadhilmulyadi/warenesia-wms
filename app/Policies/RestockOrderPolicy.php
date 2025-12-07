<?php

namespace App\Policies;

use App\Enums\RestockStatus;
use App\Models\RestockOrder;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RestockOrderPolicy
{
    use HandlesAuthorization;

    private const VIEW_ROLES = ['admin', 'manager'];

    private const MANAGE_ROLES = ['admin', 'manager'];

    public function viewAny(User $user): bool
    {
        return $this->canView($user);
    }

    public function view(User $user, RestockOrder $restock): bool
    {
        return $this->canView($user);
    }

    public function create(User $user): bool
    {
        return $this->canManage($user);
    }

    public function cancel(User $user, RestockOrder $restock): bool
    {
        return $this->canManage($user) && $restock->canBeCancelled();
    }

    public function markInTransit(User $user, RestockOrder $restock): bool
    {
        return $this->canManage($user) && $restock->canBeMarkedInTransit();
    }

    public function markReceived(User $user, RestockOrder $restock): bool
    {
        return $this->canManage($user) && $restock->canBeMarkedReceived();
    }

    public function rate(User $user, RestockOrder $restock): bool
    {
        return $this->canManage($user)
            && $restock->status === RestockStatus::RECEIVED
            && $restock->rating === null;
    }

    public function export(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager', 'staff'], true);
    }

    public function viewSupplierRestocks(User $user, mixed $restock = null): bool
    {
        if (!$user->isSupplier()) {
            return false;
        }

        if ($restock instanceof RestockOrder) {
            return (int) $restock->supplier_id === (int) $user->supplier?->id;
        }

        return true;
    }

    public function confirmSupplierRestock(User $user, RestockOrder $restock): bool
    {
        return $user->isSupplier()
            && (int) $restock->supplier_id === (int) $user->supplier?->id
            && $restock->canBeConfirmedBySupplier();
    }

    public function rejectSupplierRestock(User $user, RestockOrder $restock): bool
    {
        return $user->isSupplier()
            && (int) $restock->supplier_id === (int) $user->supplier?->id
            && $restock->canBeConfirmedBySupplier();
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
