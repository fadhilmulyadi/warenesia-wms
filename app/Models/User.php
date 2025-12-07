<?php

namespace App\Models;

use App\Enums\Role;
use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    public const ROLE_SUPER_ADMIN_ID = 1;

    public const DEFAULT_PER_PAGE = 10;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'is_approved',
        'department',
        'last_login_at',
        'approved_at',
        'approved_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_approved' => 'boolean',
            'last_login_at' => 'datetime',
            'approved_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::ADMIN->value;
    }

    public function isManager(): bool
    {
        return $this->role === Role::MANAGER->value;
    }

    public function isStaff(): bool
    {
        return $this->role === Role::STAFF->value;
    }

    public function isSupplier(): bool
    {
        return $this->role === Role::SUPPLIER->value;
    }

    public function isPending(): bool
    {
        return $this->status === UserStatus::PENDING->value;
    }

    public function isSuspended(): bool
    {
        return $this->status === UserStatus::SUSPENDED->value;
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::ACTIVE->value;
    }

    public function isSuperAdmin(): bool
    {
        return (int) $this->id === self::ROLE_SUPER_ADMIN_ID;
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'approved_by');
    }

    public function supplier(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Supplier::class);
    }

    public static function roleOptions(): array
    {
        return collect(Role::cases())
            ->mapWithKeys(fn($role) => [$role->value => $role->label()])
            ->toArray();
    }

    public static function statusOptions(): array
    {
        return collect(UserStatus::cases())
            ->mapWithKeys(fn($status) => [$status->value => $status->label()])
            ->toArray();
    }
}
