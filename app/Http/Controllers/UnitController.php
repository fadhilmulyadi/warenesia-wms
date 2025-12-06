<?php

namespace App\Http\Controllers;

use App\Http\Requests\UnitStoreRequest;
use App\Http\Requests\UnitUpdateRequest;
use App\Models\Unit;
use App\Services\UnitService;
use DomainException;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function __construct(private readonly UnitService $units) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Unit::class);

        $search = (string) $request->query('q', '');

        $units = $this->units->index([
            'search' => $search,
        ]);

        return view('units.index', compact('units', 'search'));
    }

    public function create()
    {
        $this->authorize('create', Unit::class);

        return view('units.create');
    }

    public function store(UnitStoreRequest $request)
    {
        $this->authorize('create', Unit::class);

        $this->units->create($request->validated());

        return redirect()
            ->route('units.index')
            ->with('success', 'Satuan berhasil ditambahkan.');
    }

    public function edit(Unit $unit)
    {
        $this->authorize('update', $unit);

        return view('units.edit', compact('unit'));
    }

    public function update(UnitUpdateRequest $request, Unit $unit)
    {
        $this->authorize('update', $unit);

        $this->units->update($unit, $request->validated());

        return redirect()
            ->route('units.index')
            ->with('success', 'Satuan berhasil diperbarui.');
    }

    public function destroy(Unit $unit)
    {
        $this->authorize('delete', $unit);

        try {
            $this->units->delete($unit);
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('units.index')
            ->with('success', 'Satuan berhasil dihapus.');
    }

    public function quickStore(UnitStoreRequest $request)
    {
        $this->authorize('create', Unit::class);

        $unit = $this->units->create($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'id' => $unit->id,
                'name' => $unit->name,
            ], 201);
        }

        return redirect()
            ->back()
            ->with('success', 'Satuan berhasil ditambahkan.')
            ->with('newUnitId', $unit->id);
    }
}
