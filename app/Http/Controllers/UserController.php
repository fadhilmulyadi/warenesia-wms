<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStatusUpdateRequest;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use App\Services\UserManagementService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private readonly UserManagementService $userManagement
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        [$sort, $direction] = $this->resolveSortAndDirection($request);
        $perPage = $this->resolvePerPage($request);
        $deletedFilter = $this->resolveDeletedFilter($request);
        $search = trim((string) $request->query('q', ''));
        $roleFilter = (array) $request->query('role', []);
        $statusFilter = (array) $request->query('status', []);

        $users = $this->userManagement->list([
            'search' => $search,
            'role' => $roleFilter,
            'status' => $statusFilter,
            'sort' => $sort,
            'direction' => $direction,
            'per_page' => $perPage,
            'deleted' => $deletedFilter,
        ]);

        $deletionGuards = collect($users->items())
            ->mapWithKeys(fn (User $user) => [
                $user->id => $this->userManagement->deletionGuardReason($user, $request->user()),
            ]);

        return view('users.index', [
            'users' => $users,
            'roles' => User::roleOptions(),
            'statuses' => User::statusOptions(),
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
            'perPage' => $perPage,
            'deletedFilter' => $deletedFilter,
            'roleFilter' => $roleFilter,
            'statusFilter' => $statusFilter,
            'deletionGuards' => $deletionGuards,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('users.create', [
            'roles' => User::roleOptions(),
            'statuses' => User::statusOptions(),
        ]);
    }

    public function store(UserStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $this->userManagement->create($request->validated());

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(Request $request, User $user): View
    {
        $this->authorize('update', $user);

        return view('users.edit', [
            'user' => $user,
            'roles' => User::roleOptions(),
            'statuses' => User::statusOptions(),
            'deletionReason' => $this->userManagement->deletionGuardReason($user, $request->user()),
        ]);
    }

    public function update(UserUpdateRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        try {
            $this->userManagement->update($user, $request->validated());
        } catch (DomainException $exception) {
            return back()
                ->withErrors(['role' => $exception->getMessage()])
                ->withInput();
        }

        return redirect()
            ->route('users.edit', $user)
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        try {
            $this->userManagement->delete($user);
        } catch (DomainException $exception) {
            return back()->withErrors(['delete' => $exception->getMessage()]);
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil dihapus.');
    }

    public function approveSupplier(UserStatusUpdateRequest $request, User $user): RedirectResponse
    {
        $this->authorize('approveSupplier', $user);

        try {
            $this->userManagement->approveSupplier($user, $request->user());
        } catch (DomainException|\Throwable $exception) {
            return back()->withErrors(['approve' => $exception->getMessage()]);
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'Supplier berhasil disetujui.');
    }

    private function resolveSortAndDirection(Request $request): array
    {
        $allowedSorts = ['created_at', 'last_login_at', 'name', 'email'];

        $sort = $request->query('sort', 'created_at');
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }

        $direction = strtolower((string) $request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        return [$sort, $direction];
    }

    private function resolvePerPage(Request $request): int
    {
        $perPage = (int) $request->query('per_page', User::DEFAULT_PER_PAGE);

        return max(5, min(50, $perPage));
    }

    private function resolveDeletedFilter(Request $request): string
    {
        $deleted = $request->query('deleted', 'active');

        return in_array($deleted, ['with', 'only', 'active'], true) ? $deleted : 'active';
    }
}
