<?php

namespace App\Services;

use App\Models\Unit;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class UnitService
{
    private const PER_PAGE = 15;

    public function index(array $filters = []): LengthAwarePaginator
    {
        $query = $this->query($filters);

        return $query->paginate(self::PER_PAGE)->withQueryString();
    }

    public function query(array $filters = []): Builder
    {
        $search = trim((string) ($filters['search'] ?? ''));

        $query = Unit::query()->withCount('products');

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        return $query->orderBy('name');
    }

    public function create(array $data): Unit
    {
        $name = trim((string) $data['name']);

        $existing = Unit::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();

        if ($existing) {
            return $existing;
        }

        return Unit::create([
            'name' => $name,
            'description' => $data['description'] ?? null,
        ]);
    }

    public function update(Unit $unit, array $data): Unit
    {
        $unit->update([
            'name' => trim((string) $data['name']),
            'description' => $data['description'] ?? null,
        ]);

        return $unit->refresh();
    }

    public function delete(Unit $unit): void
    {
        if ($unit->products()->exists()) {
            throw new DomainException('Satuan tidak dapat dihapus karena masih digunakan oleh produk.');
        }

        $unit->delete();
    }
}