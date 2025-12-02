@extends('layouts.app')

@section('title', 'Tambah User')

@section('page-header')
    <div class="hidden md:block">
        <x-page-header title="Tambah User" description="Buat akun pengguna baru untuk WMS." />
    </div>
    <div class="md:hidden">
        <x-mobile-header title="Tambah User" back="{{ route('users.index') }}" />
    </div>
@endsection

@section('content')
    <div class="max-w-6xl mx-auto">
        {{-- MOBILE VERSION --}}
        {{-- MOBILE VERSION --}}
        <x-mobile.form form-id="user-form-mobile" save-label="Simpan User" save-icon="save">
            <x-slot:fields>
                @if($errors->any())
                    <div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
                        Terdapat kesalahan input. Periksa kembali formulir di bawah.
                    </div>
                @endif

                <x-card>
                    <div class="p-4 space-y-4">
                        <form id="user-form-mobile" method="POST" action="{{ route('users.store') }}" class="space-y-4">
                            @csrf
                            @include('users._form', ['user' => null, 'roles' => $roles, 'statuses' => $statuses])
                        </form>
                    </div>
                </x-card>
            </x-slot:fields>
        </x-mobile.form>

        {{-- DESKTOP VERSION --}}
        <div class="hidden md:block space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <x-breadcrumbs :items="['Users' => route('users.index'), 'Tambah User' => '#']" />

                <div class="flex flex-wrap gap-2">
                    <x-action-button href="{{ route('users.index') }}" variant="secondary" icon="arrow-left">
                        Kembali
                    </x-action-button>

                    <x-action-button type="submit" form="user-form" variant="primary" icon="save">
                        Simpan User
                    </x-action-button>
                </div>
            </div>

            <x-card>
                <div class="p-4 sm:p-6 space-y-4">
                    <div class="flex flex-col gap-1">
                        <h2 class="text-base font-semibold text-slate-900">Informasi Akun</h2>
                        <p class="text-xs text-slate-500">Lengkapi detail user, role, dan status aktif.</p>
                    </div>

                    @if($errors->any())
                        <div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
                            Terdapat kesalahan input. Periksa kembali formulir di bawah.
                        </div>
                    @endif

                    <form id="user-form" method="POST" action="{{ route('users.store') }}" class="space-y-4">
                        @csrf
                        @include('users._form', ['user' => null, 'roles' => $roles, 'statuses' => $statuses])
                    </form>
                </div>
            </x-card>
        </div>
    </div>
@endsection