<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ActivityLogService
{
    public function log(?User $user, string $action, string $description, ?Model $subject = null): void
    {
        ActivityLog::create([
            'user_id' => $user?->id,
            'action' => strtoupper($action),
            'description' => $description,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'created_at' => now(),
        ]);
    }
}
