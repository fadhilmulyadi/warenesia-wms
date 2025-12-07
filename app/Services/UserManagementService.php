<?php

namespace App\Services;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\IncomingTransaction;
use App\Models\OutgoingTransaction;
use App\Models\RestockOrder;
use App\Models\User;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

class UserManagementService
{
    public function __construct(
        private readonly SupplierProfileService $supplierProfiles,
        private readonly SupplierService $suppliers
    ) {}

    public function list(array $filters = []): LengthAwarePaginator
    {
        $filters = $this->normaliseFilters($filters);

        $query = User::query()
            ->with(['approvedBy']);

        if ($filters['deleted'] === 'with') {
            $query->withTrashed();
        } elseif ($filters['deleted'] === 'only') {
            $query->onlyTrashed();
        }

        $this->applyFilters($query, $filters);

        $query->orderBy($filters['sort'], $filters['direction'])
            ->orderBy('id');

        return $query
            ->paginate($filters['per_page'])
            ->withQueryString();
    }

    public function create(array $data): User
    {
        $payload = $this->mapBasePayload($data);
        $payload['password'] = Hash::make($data['password']);

        return DB::transaction(function () use ($payload, $data): User {
            $user = User::create($payload);

            if ($user->role === Role::SUPPLIER->value) {
                $this->supplierProfiles->sync($user, $data, $user->status === UserStatus::ACTIVE->value);
            }

            return $user->refresh();
        });
    }

    public function update(User $user, array $data): User
    {
        $payload = $this->mapBasePayload($data, $user);

        return DB::transaction(function () use ($user, $payload, $data): User {
            $user->update($payload);

            if (! empty($data['password'])) {
                $this->resetPassword($user, $data['password']);
            }

            if ($user->role === Role::SUPPLIER->value) {
                $this->supplierProfiles->sync($user, $data, $user->status === UserStatus::ACTIVE->value);
            }

            return $user->refresh();
        });
    }

    public function delete(User $user): void
    {
        $reason = $this->deletionGuardReason($user, Auth::user());

        if ($reason !== null) {
            throw new DomainException($reason);
        }

        $user->delete();
    }

    public function resetPassword(User $user, string $password): void
    {
        $user->forceFill([
            'password' => Hash::make($password),
        ])->save();
    }

    public function approveSupplier(User $user, ?User $approver = null): void
    {
        $approver ??= Auth::user();

        if ($user->role !== Role::SUPPLIER->value) {
            throw new InvalidArgumentException('Hanya akun supplier yang dapat disetujui.');
        }

        if ($user->status !== UserStatus::PENDING->value) {
            throw new DomainException('Supplier tidak dalam status pending.');
        }

        $this->suppliers->approve($user, $approver ?? $user);
    }

    public function markLogin(User $user): void
    {
        $user->forceFill([
            'last_login_at' => now(),
        ])->save();
    }

    public function deletionGuardReason(User $target, ?User $actor = null): ?string
    {
        if ($target->isSuperAdmin()) {
            return 'User ini dilindungi sebagai super admin.';
        }

        if ($actor !== null && (int) $actor->id === (int) $target->id) {
            return 'Anda tidak dapat menghapus akun sendiri.';
        }

        if ($target->role === Role::ADMIN->value && ! $this->hasAnotherActiveAdmin($target)) {
            return 'Tidak dapat menghapus admin terakhir.';
        }

        if ($this->hasActiveTransactions($target)) {
            return 'User masih memiliki transaksi aktif.';
        }

        return null;
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if ($filters['search'] !== '') {
            $keyword = $filters['search'];
            $query->where(function (Builder $builder) use ($keyword): void {
                $builder->where('name', 'like', '%'.$keyword.'%')
                    ->orWhere('email', 'like', '%'.$keyword.'%');
            });
        }

        if (is_array($filters['role']) && $filters['role'] !== []) {
            $query->whereIn('role', $filters['role']);
        }

        if (is_array($filters['status']) && $filters['status'] !== []) {
            $query->whereIn('status', $filters['status']);
        }
    }

    private function mapBasePayload(array $data, ?User $existing = null): array
    {
        $role = $data['role'];
        $status = $data['status'];

        if ($existing?->isSuperAdmin()) {
            $role = Role::ADMIN->value;
            $status = UserStatus::ACTIVE->value;
        }

        if ($existing !== null
            && $existing->role === Role::ADMIN->value
            && ($role !== Role::ADMIN->value || $status !== UserStatus::ACTIVE->value)
            && ! $this->hasAnotherActiveAdmin($existing)
        ) {
            throw new DomainException('Tidak dapat menurunkan atau menonaktifkan admin terakhir.');
        }

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $role,
            'department' => $data['department'] ?? null,
            'status' => $status,
            'is_approved' => $status === UserStatus::ACTIVE->value,
        ];

        if ($role === Role::SUPPLIER->value && $status === UserStatus::ACTIVE->value && $existing?->approved_at === null) {
            $payload['approved_at'] = now();
            $payload['approved_by'] = Auth::id();
        }

        return $payload;
    }

    private function normaliseFilters(array $filters): array
    {
        $allowedSorts = ['created_at', 'last_login_at', 'name', 'email'];
        $sort = $filters['sort'] ?? 'created_at';
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }

        $direction = strtolower((string) ($filters['direction'] ?? 'desc'));
        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $perPage = (int) ($filters['per_page'] ?? User::DEFAULT_PER_PAGE);
        $perPage = max(5, min(50, $perPage));

        $deleted = $filters['deleted'] ?? 'active';
        if (! in_array($deleted, ['with', 'only', 'active'], true)) {
            $deleted = 'active';
        }

        $role = $this->normaliseFilterValues($filters['role'] ?? null, Role::values());
        $status = $this->normaliseFilterValues($filters['status'] ?? null, UserStatus::values());

        return [
            'search' => trim((string) ($filters['search'] ?? '')),
            'role' => $role,
            'status' => $status,
            'sort' => $sort,
            'direction' => $direction,
            'per_page' => $perPage,
            'deleted' => $deleted,
        ];
    }

    private function normaliseFilterValues(mixed $value, array $allowed): ?array
    {
        if ($value === null) {
            return null;
        }

        $candidates = is_array($value) ? $value : [$value];
        $filtered = collect($candidates)
            ->map(static fn ($item) => (string) $item)
            ->filter(static fn ($item) => $item !== '')
            ->filter(static fn ($item) => in_array($item, $allowed, true))
            ->unique()
            ->values()
            ->all();

        return $filtered === [] ? null : $filtered;
    }

    private function hasAnotherActiveAdmin(User $except): bool
    {
        return User::query()
            ->where('role', Role::ADMIN->value)
            ->whereNull('deleted_at')
            ->where('id', '!=', $except->id)
            ->exists();
    }

    private function hasActiveTransactions(User $user): bool
    {
        $incoming = IncomingTransaction::query()
            ->where(function ($query) use ($user): void {
                $query->where('created_by', $user->id)
                    ->orWhere('verified_by', $user->id);
            })
            ->whereIn('status', [
                IncomingTransaction::STATUS_PENDING,
                IncomingTransaction::STATUS_VERIFIED,
            ])
            ->exists();

        $outgoing = OutgoingTransaction::query()
            ->where(function ($query) use ($user): void {
                $query->where('created_by', $user->id)
                    ->orWhere('approved_by', $user->id);
            })
            ->whereIn('status', [
                OutgoingTransaction::STATUS_PENDING,
                OutgoingTransaction::STATUS_APPROVED,
            ])
            ->exists();

        $restocks = RestockOrder::query()
            ->where(function ($query) use ($user): void {
                $query->where('created_by', $user->id)
                    ->orWhere('confirmed_by', $user->id)
                    ->orWhere('rating_given_by', $user->id);
            })
            ->whereIn('status', [
                RestockOrder::STATUS_PENDING,
                RestockOrder::STATUS_CONFIRMED,
                RestockOrder::STATUS_IN_TRANSIT,
            ])
            ->exists();

        return $incoming || $outgoing || $restocks;
    }
}
