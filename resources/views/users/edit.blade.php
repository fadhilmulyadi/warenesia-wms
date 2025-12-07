@extends('layouts.app')

@section('title', 'Edit User')

@section('page-header')
    {{-- PAGE HEADER: Desktop --}}
    <div class="hidden md:block">
        <x-page-header title="Edit User" description="Perbarui data profil dan status aktif pengguna" />
    </div>

    {{-- PAGE HEADER: Mobile --}}
    <x-mobile-header class="md:hidden" title="Edit User" back="{{ route('users.index') }}" />
@endsection


@section('content')
    {{-- MOBILE FORM --}}
    <x-mobile.form form-id="user-form-mobile" save-label="Simpan Perubahan" save-icon="save">
        <x-slot:fields>
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

            <form id="user-form-mobile" method="POST" action="{{ route('users.update', $user) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <x-card>
                    <div class="p-4 space-y-4">
                        @include('users.form', ['user' => $user, 'roles' => $roles, 'statuses' => $statuses])

                        <div class="border-t border-slate-100 pt-3 text-xs text-slate-500 space-y-1 mt-4">
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
        </x-slot:fields>
    </x-mobile.form>

    {{-- PAGE CONTENT --}}
    <div class="hidden md:block space-y-4 max-w-6xl mx-auto pb-24">
        {{-- TOOLBAR --}}
        <div class="flex items-center justify-between flex-wrap gap-3">
            <x-breadcrumbs :items="['Pengguna' => route('users.index'), $user->name => route('users.edit', $user), 'Edit' => '#']" />

            <div class="flex flex-wrap gap-2">
                <x-action-button href="{{ route('users.index') }}" variant="secondary" icon="arrow-left">
                    Kembali
                </x-action-button>

                @can('approveSupplier', $user)
                    @if($user->role === \App\Enums\Role::SUPPLIER->value && $user->status === \App\Enums\UserStatus::PENDING->value)
                        <form method="POST" action="{{ route('users.approve', $user) }}">
                            @csrf
                            @method('PATCH')
                            <x-action-button type="submit" variant="primary" icon="check">
                                Approve Supplier
                            </x-action-button>
                        </form>
                    @endif
                @endcan



                <x-action-button type="submit" form="user-form" variant="primary" icon="save">
                    Simpan Perubahan
                </x-action-button>
            </div>
        </div>

        {{-- ALERTS --}}
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

        {{-- FORM --}}
        <form id="user-form" method="POST" action="{{ route('users.update', $user) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <x-card>
                <div class="p-4 sm:p-6 space-y-6">
                    <div class="flex flex-col gap-1">
                        <h2 class="text-base font-semibold text-slate-900">INFORMASI PENGGUNA</h2>
                        <p class="text-xs text-slate-500">Perbarui profil, role, status, dan password jika diperlukan.</p>
                    </div>

                    @include('users.form', ['user' => $user, 'roles' => $roles, 'statuses' => $statuses])

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