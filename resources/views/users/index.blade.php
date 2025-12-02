@extends('layouts.app')

@section('title', 'User')

@section('page-header')
    <x-page-header title="Manajemen User" description="Kelola seluruh pengguna sistem." />
@endsection

@section('content')
    <div class="max-w-6xl mx-auto">
        @php
            $statusVariants = [
                'active' => 'success',
                'pending' => 'warning',
                'suspended' => 'danger',
            ];
        @endphp

        {{-- MOBILE --}}
        <div class="md:hidden">
            @php
                $mobileIndexConfig = \App\Support\MobileIndexConfig::users($roles, $statuses);
            @endphp

            <x-mobile.index :items="$users" :config="$mobileIndexConfig" card-view="mobile.users.card" :extra-data="[
                                'roles' => $roles,
                                'statuses' => $statuses,
                                'statusVariants' => $statusVariants ?? [],
                                'deletionGuards' => $deletionGuards ?? []
                            ]" />
        </div>

        <div class="hidden md:block space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <x-breadcrumbs :items="[
            'Users' => route('users.index')
        ]" />
            </div>

            <x-toolbar>
                @php
                    $filters = [
                        'role' => 'Role',
                        'status' => 'Status',
                    ];
                    $resetKeys = array_keys($filters);
                @endphp

                <x-filter-bar :action="route('users.index', ['per_page' => $perPage])" :search="$search" :sort="$sort"
                    :direction="$direction" :filters="$filters" :resetKeys="$resetKeys"
                    placeholder="Cari nama atau email...">
                    <x-slot:filter_role>
                        <x-filter.checkbox-list name="role" :columns="2" :options="collect($roles)->map(fn($label, $value) => ['value' => $value, 'label' => $label])" :selected="$roleFilter" />
                    </x-slot:filter_role>

                    <x-slot:filter_status>
                        <x-filter.checkbox-list name="status" :columns="2" :options="collect($statuses)->map(fn($label, $value) => ['value' => $value, 'label' => $label])" :selected="$statusFilter" />
                    </x-slot:filter_status>
                </x-filter-bar>

                <div class="flex flex-wrap flex-none gap-2 justify-end">
                    <x-action-button href="{{ route('users.create') }}" variant="primary" icon="plus">
                        Tambah User
                    </x-action-button>
                </div>
            </x-toolbar>

            <x-card class="p-0 overflow-hidden">
                <div class="w-full overflow-x-auto">
                    <x-table>
                        <x-table.thead>
                            <x-table.th sortable name="name">Nama</x-table.th>
                            <x-table.th>Email</x-table.th>
                            <x-table.th>Role</x-table.th>
                            <x-table.th>Status</x-table.th>
                            <x-table.th sortable name="created_at">Dibuat</x-table.th>
                            <x-table.th sortable name="last_login_at">Terakhir Login</x-table.th>
                            <x-table.th align="right">Aksi</x-table.th>
                        </x-table.thead>

                        <x-table.tbody>
                            @forelse($users as $user)
                                @php
                                    $guardReason = $deletionGuards[$user->id] ?? null;
                                    $roleLabel = $roles[$user->role] ?? ucfirst($user->role);
                                    $statusLabel = $statuses[$user->status] ?? ucfirst($user->status);
                                    $statusVariant = $statusVariants[$user->status] ?? 'neutral';
                                @endphp

                                <x-table.tr>
                                    <x-table.td class="font-semibold text-slate-900">
                                        <div class="flex items-center gap-2">
                                            {{ $user->name }}
                                            @if($user->trashed())
                                                <x-badge variant="gray">Terhapus</x-badge>
                                            @endif
                                        </div>
                                    </x-table.td>
                                    <x-table.td class="text-slate-600">
                                        {{ $user->email }}
                                    </x-table.td>
                                    <x-table.td>
                                        <x-badge variant="blue">{{ $roleLabel }}</x-badge>
                                    </x-table.td>
                                    <x-table.td>
                                        <x-badge :variant="$statusVariant">
                                            {{ $statusLabel }}
                                        </x-badge>
                                    </x-table.td>
                                    <x-table.td class="whitespace-nowrap">
                                        {{ $user->created_at?->format('d M Y') ?? '-' }}
                                    </x-table.td>
                                    <x-table.td class="whitespace-nowrap">
                                        {{ $user->last_login_at?->format('d M Y H:i') ?? 'Belum login' }}
                                    </x-table.td>

                                    <x-table.td align="right">
                                        <x-table.actions>
                                            <x-table.action-item icon="pencil" href="{{ route('users.edit', $user) }}">
                                                Edit
                                            </x-table.action-item>

                                            @can('approveSupplier', $user)
                                                @if($user->role === \App\Enums\Role::SUPPLIER->value && $user->status === \App\Enums\UserStatus::PENDING->value)
                                                    <form method="POST" action="{{ route('users.approve', $user) }}" class="m-0">
                                                        @csrf
                                                        @method('PATCH')
                                                        <x-table.action-item type="submit" icon="check">
                                                            Approve
                                                        </x-table.action-item>
                                                    </form>
                                                @endif
                                            @endcan

                                            @if(auth()->user()->can('delete', $user) && !$guardReason)
                                                <x-table.action-item icon="trash-2" danger="true" x-on:click="$dispatch('open-delete-modal', { 
                                                                                                                    action: '{{ route('users.destroy', $user) }}',
                                                                                                                    title: 'Hapus User',
                                                                                                                    message: 'Yakin ingin menghapus user ini?',
                                                                                                                    itemName: '{{ addslashes($user->name) }}'
                                                                                                                })">
                                                    Hapus
                                                </x-table.action-item>
                                            @else
                                                <x-table.action-item icon="ban" disabled
                                                    title="{{ $guardReason ?? 'Tidak diizinkan' }}"
                                                    class="opacity-40 cursor-not-allowed">
                                                    Hapus
                                                </x-table.action-item>
                                            @endif
                                        </x-table.actions>
                                    </x-table.td>
                                </x-table.tr>
                            @empty
                                <x-table.tr>
                                    <x-table.td colspan="7" class="text-center text-slate-500 py-6">
                                        Belum ada user yang memenuhi filter.
                                    </x-table.td>
                                </x-table.tr>
                            @endforelse
                        </x-table.tbody>
                    </x-table>
                </div>

                @if($users->hasPages())
                    <div class="p-4 border-t border-slate-200">
                        <x-advanced-pagination :paginator="$users" />
                    </div>
                @endif
            </x-card>
        </div>

        <x-confirm-delete-modal />
    </div>
@endsection