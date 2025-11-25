<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReportPolicy
{
    use HandlesAuthorization;

    public function viewTransactionsReport(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager'], true);
    }
}
