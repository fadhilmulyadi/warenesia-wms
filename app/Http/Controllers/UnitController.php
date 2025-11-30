<?php

namespace App\Http\Controllers;

use App\Http\Requests\UnitRequest;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    private const PER_PAGE = 15;

    public function index(Request $request)
    {
        $search = (string) $request->query('q', '');

        $units = Unit::query()
            ->withCount('products')
            ->when($search, static function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('name')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('units.index', compact('units', 'search'));
    }

    public function create()
    {
        return view('units.create');
    }

    public function store(UnitRequest $request)
    {
        $data = $request->validated();

        Unit::create($data);

        return redirect()
            ->route('units.index')
            ->with('success', 'Satuan berhasil ditambahkan.');
    }

    public function edit(Unit $unit)
    {
        return view('units.edit', compact('unit'));
    }

    public function update(UnitRequest $request, Unit $unit)
    {
        $unit->update($request->validated());

        return redirect()
            ->route('units.index')
            ->with('success', 'Satuan berhasil diperbarui.');
    }

    public function destroy(Unit $unit)
    {
        if ($unit->products()->exists()) {
            return back()->with('error', 'Satuan tidak dapat dihapus karena digunakan oleh produk.');
        }

        $unit->delete();

        return redirect()
            ->route('units.index')
            ->with('success', 'Satuan berhasil dihapus.');
    }

    public function quickStore(UnitRequest $request)
    {
        $unit = Unit::create($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'id'   => $unit->id,
                'name' => $unit->name,
            ], 201);
        }

        return redirect()
            ->back()
            ->with('success', 'Satuan berhasil dibuat.')
            ->with('newUnitId', $unit->id);
    }
}
