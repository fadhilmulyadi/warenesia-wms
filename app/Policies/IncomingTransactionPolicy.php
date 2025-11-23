<?php

namespace App\Policies;

use App\Models\IncomingTransaction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class IncomingTransactionPolicy
{
    use HandlesAuthorization;

    private const CREATOR_ROLES = ['admin', 'manager', 'staff'];
    private const APPROVER_ROLES = ['admin', 'manager'];

    public function viewAny(User $user): bool
    {
        return $this->canCreateOrView($user);
    }

    public function view(User $user, IncomingTransaction $transaction): bool
    {
        return $this->canCreateOrView($user);
    }

    public function create(User $user): bool
    {
        return $this->canCreateOrView($user);
    }

    public function update(User $user, IncomingTransaction $transaction): bool
    {
        if ($user->role === 'staff') {
            return $this->staffOwnsPending($user, $transaction);
        }

        return $this->isApprover($user);
    }

    public function delete(User $user, IncomingTransaction $transaction): bool
    {
        if ($user->role === 'staff') {
            return $this->staffOwnsPending($user, $transaction);
        }

        return $this->isApprover($user);
    }

    public function verify(User $user, IncomingTransaction $transaction): bool
    {
        return $this->isApprover($user) && $transaction->canBeVerified();
    }

    public function reject(User $user, IncomingTransaction $transaction): bool
    {
        return $this->isApprover($user) && $transaction->canBeRejected();
    }

    public function complete(User $user, IncomingTransaction $transaction): bool
    {
        return $this->isApprover($user) && $transaction->canBeCompleted();
    }

    public function export(User $user): bool
    {
        // Scope exported rows appropriately in controllers (staff should only see their own data).
        return $this->canCreateOrView($user);
    }

    private function canCreateOrView(User $user): bool
    {
        return in_array($user->role, self::CREATOR_ROLES, true);
    }

    private function isApprover(User $user): bool
    {
        return in_array($user->role, self::APPROVER_ROLES, true);
    }

    private function staffOwnsPending(User $user, IncomingTransaction $transaction): bool
    {
        return $transaction->status === IncomingTransaction::STATUS_PENDING
            && (int) $transaction->created_by === (int) $user->id;
    }
}
