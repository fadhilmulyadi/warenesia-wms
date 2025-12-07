<?php

namespace App\Services;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\Supplier;
use App\Models\User;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

class SupplierService
{
    private const DEFAULT_PER_PAGE = Supplier::DEFAULT_PER_PAGE;

    private const MAX_PER_PAGE = 250;

    public function __construct(private readonly SupplierProfileService $profiles)
    {
    }

    public function index(array $filters = []): LengthAwarePaginator
    {
        $query = $this->query($filters);
        $perPage = $this->resolvePerPage($filters['per_page'] ?? null);

        return $query->paginate($perPage)->withQueryString();
    }

    public function query(array $filters = []): Builder
    {
        $filters = $this->normaliseFilters($filters);

        $query = Supplier::query()
            ->withCount([
                'restockOrders as rated_restock_count' => function ($builder): void {
                    $builder->whereNotNull('rating');
                },
            ])
            ->withAvg('restockOrders as average_rating', 'rating');

        if ($filters['search'] !== '') {
            $keyword = $filters['search'];
            $query->where(function (Builder $builder) use ($keyword): void {
                $builder->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('contact_person', 'like', '%' . $keyword . '%')
                    ->orWhere('email', 'like', '%' . $keyword . '%')
                    ->orWhere('phone', 'like', '%' . $keyword . '%');
            });
        }

        $query->orderBy($filters['sort'], $filters['direction'])
            ->orderBy('id');

        return $query;
    }

    public function create(array $data): Supplier
    {
        throw new DomainException('Penambahan supplier baru harus dilakukan melalui menu User Management.');
    }

    public function update(Supplier $supplier, array $data): Supplier
    {
        $supplier->update($this->mapPayload($data, $supplier));

        if ($supplier->user_id) {
            $updates = [];
            if (isset($data['email']) && $data['email'] !== $supplier->user->email) {
                $updates['email'] = $data['email'];
            }
            if (isset($data['name']) && $data['name'] !== $supplier->user->name) {
                $updates['name'] = $data['name'];
            }
            if (!empty($updates)) {
                $supplier->user->update($updates);
            }
        }

        return $supplier->refresh();
    }

    public function delete(Supplier $supplier): void
    {
        if ($supplier->products()->exists() || $supplier->restockOrders()->exists()) {
            throw new DomainException('Supplier tidak dapat dihapus karena memiliki produk atau restock.');
        }

        $supplier->delete();
    }

    public function register(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $user = User::create([
                'name' => $data['supplier_name'],
                'email' => $data['email'],
                'role' => Role::SUPPLIER->value,
                'department' => $data['category_of_goods'] ?? null,
                'status' => UserStatus::PENDING->value,
                'is_approved' => false,
                'password' => Hash::make($data['password']),
            ]);

            $this->profiles->sync($user, $data);

            return $user;
        });
    }

    public function approve(User $user, User $approver): void
    {
        if ($user->role !== Role::SUPPLIER->value || $user->status !== UserStatus::PENDING->value) {
            throw new InvalidArgumentException('Hanya supplier dengan status pending yang dapat di-approve.');
        }

        $user->update([
            'status' => UserStatus::ACTIVE->value,
            'approved_at' => now(),
            'is_approved' => true,
        ]);

        $this->profiles->sync($user, []);
    }

    private function mapPayload(array $data, ?Supplier $supplier = null): array
    {
        return [
            'name' => trim((string) $data['name']),
            'contact_person' => $data['contact_person'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'tax_number' => $data['tax_number'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'country' => $data['country'] ?? 'Indonesia',
            'notes' => $data['notes'] ?? null,
        ];
    }

    private function normaliseFilters(array $filters): array
    {
        $allowedSorts = ['name', 'average_rating', 'rated_restock_count', 'created_at'];
        $sort = $filters['sort'] ?? 'name';
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'name';
        }

        $direction = strtolower((string) ($filters['direction'] ?? 'asc'));
        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        return [
            'search' => trim((string) ($filters['search'] ?? '')),
            'sort' => $sort,
            'direction' => $direction,
        ];
    }

    private function resolvePerPage(?int $perPage): int
    {
        if ($perPage === null || $perPage <= 0) {
            return self::DEFAULT_PER_PAGE;
        }

        return min($perPage, self::MAX_PER_PAGE);
    }
}
