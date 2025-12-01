@extends('layouts.app')

@section('title', 'Edit User')

@section('page-header')
    <x-page-header
        title="Edit User"
        description="Perbarui profil dan status akun pengguna."
    />
@endsection

@section('content')
    <div class="space-y-4 max-w-6xl">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <x-breadcrumbs :items="['Users' => route('users.index'), $user->name => '#']" />

            <div class="flex flex-wrap gap-2">
                <x-action-button
                    href="{{ route('users.index') }}"
                    variant="secondary"
                    icon="arrow-left"
                >
                    Kembali
                </x-action-button>

                @can('approveSupplier', $user)
                    @if($user->role === \App\Enums\Role::SUPPLIER->value && $user->status === \App\Enums\UserStatus::PENDING->value)
                        <form method="POST" action="{{ route('users.approve', $user) }}">
                            @csrf
                            @method('PATCH')
                            <x-action-button
                                type="submit"
                                variant="primary"
                                icon="check"
                            >
                                Approve Supplier
                            </x-action-button>
                        </form>
                    @endif
                @endcan

                @if(!$deletionReason && auth()->user()->can('delete', $user))
                    <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Hapus user ini? Data akan disoft delete.');">
                        @csrf
                        @method('DELETE')
                        <x-action-button
                            type="submit"
                            variant="outline-danger"
                            icon="trash-2"
                        >
                            Hapus
                        </x-action-button>
                    </form>
                @else
                    <x-action-button
                        variant="ghost"
                        icon="ban"
                        disabled
                        title="{{ $deletionReason ?? 'Tidak diizinkan' }}"
                    >
                        Hapus
                    </x-action-button>
                @endif

                <x-action-button
                    type="submit"
                    form="user-form"
                    variant="primary"
                    icon="save"
                >
                    Simpan Perubahan
                </x-action-button>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
                Terdapat kesalahan input. Periksa kembali formulir di bawah.
            </div>
        @endif

        <form id="user-form" method="POST" action="{{ route('users.update', $user) }}" class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            @csrf
            @method('PUT')

            {{-- Profil & Identitas --}}
            <x-card class="lg:col-span-2">
                <div class="p-4 sm:p-6 space-y-4">
                    <div class="flex flex-col gap-1">
                        <h2 class="text-base font-semibold text-slate-900">Informasi Pengguna</h2>
                        <p class="text-xs text-slate-500">Nama, email, departemen, dan role.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <x-input-label for="name" value="Nama" />
                            <x-text-input
                                id="name"
                                name="name"
                                type="text"
                                value="{{ old('name', $user->name) }}"
                                required
                                class="block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500"
                            />
                            <x-input-error :messages="$errors->get('name')" />
                        </div>

                        <div class="space-y-2">
                            <x-input-label for="email" value="Email" />
                            <x-text-input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email', $user->email) }}"
                                required
                                class="block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500"
                            />
                            <x-input-error :messages="$errors->get('email')" />
                        </div>

                        <div class="space-y-2">
                            <x-input-label for="department" value="Departemen (Opsional)" />
                            <x-text-input
                                id="department"
                                name="department"
                                type="text"
                                value="{{ old('department', $user->department) }}"
                                class="block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500"
                            />
                            <x-input-error :messages="$errors->get('department')" />
                        </div>

                        <div class="space-y-2">
                            <x-input-label for="role" value="Role" />
                            <x-custom-select
                                name="role"
                                id="role"
                                :options="$roles"
                                :value="old('role', $user->role)"
                                placeholder="Pilih role"
                                width="w-full"
                            />
                            <x-input-error :messages="$errors->get('role')" />
                        </div>
                    </div>
                </div>
            </x-card>

            {{-- Status & Keamanan --}}
            <x-card class="lg:col-span-1">
                <div class="p-4 sm:p-6 space-y-4">
                    <div class="flex flex-col gap-1">
                        <h2 class="text-base font-semibold text-slate-900">Status & Keamanan</h2>
                        <p class="text-xs text-slate-500">Kelola status akun dan reset password.</p>
                    </div>

                    <div class="space-y-2">
                        <x-input-label for="status" value="Status" />
                        <x-custom-select
                            name="status"
                            id="status"
                            :options="$statuses"
                            :value="old('status', $user->status)"
                            placeholder="Pilih status"
                            width="w-full"
                        />
                        <x-input-error :messages="$errors->get('status')" />
                    </div>

                    <div class="space-y-2" x-data="{ generate() {
                        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%';
                        let pwd = '';
                        for (let i = 0; i < 10; i++) {
                            pwd += chars.charAt(Math.floor(Math.random() * chars.length));
                        }
                        $refs.password.value = pwd;
                        if ($refs.password_confirmation) {
                            $refs.password_confirmation.value = pwd;
                        }
                    }}">
                        <div class="flex items-center justify-between">
                            <x-input-label for="password" value="Reset Password (opsional)" />
                            <button type="button" @click="generate()" class="text-[11px] font-semibold text-teal-700 hover:text-teal-800">
                                Generate
                            </button>
                        </div>
                        <x-text-input
                            x-ref="password"
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="new-password"
                            class="block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500"
                        />
                        <x-input-error :messages="$errors->get('password')" />
                    </div>

                    <div class="space-y-2">
                        <x-input-label for="password_confirmation" value="Konfirmasi Password Baru" />
                        <x-text-input
                            x-ref="password_confirmation"
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            class="block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500"
                        />
                        <x-input-error :messages="$errors->get('password_confirmation')" />
                    </div>

                    <div class="border-t border-slate-100 pt-3 text-xs text-slate-500 space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-slate-700">Dibuat:</span>
                            <span>{{ optional($user->created_at)->format('d M Y, H:i') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-slate-700">Login terakhir:</span>
                            <span>{{ optional($user->last_login_at)->format('d M Y, H:i') ?? 'Belum ada data' }}</span>
                        </div>
                        @if($user->approved_at)
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-slate-700">Disetujui:</span>
                                <span>{{ optional($user->approved_at)->format('d M Y, H:i') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </x-card>
        </form>
    </div>
@endsection