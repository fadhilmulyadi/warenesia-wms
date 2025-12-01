@props([
    'user' => null,
    'roles' => [],
    'statuses' => [],
])

@php
    $isEdit = $user !== null;
    $defaultRole = \App\Enums\Role::STAFF->value;
    $defaultStatus = \App\Enums\UserStatus::ACTIVE->value;
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="space-y-2">
        <x-input-label for="name" value="Nama" />
        <x-text-input
            id="name"
            name="name"
            type="text"
            value="{{ old('name', $user->name ?? '') }}"
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
            value="{{ old('email', $user->email ?? '') }}"
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
            value="{{ old('department', $user->department ?? '') }}"
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
            :value="old('role', $user->role ?? $defaultRole)"
            placeholder="Pilih role"
            width="w-full"
        />
        <x-input-error :messages="$errors->get('role')" />
    </div>

    <div class="space-y-2">
        <x-input-label for="status" value="Status" />
        <x-custom-select
            name="status"
            id="status"
            :options="$statuses"
            :value="old('status', $user->status ?? $defaultStatus)"
            placeholder="Pilih status"
            width="w-full"
        />
        <x-input-error :messages="$errors->get('status')" />
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
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
            <x-input-label for="password" :value="$isEdit ? 'Reset Password (opsional)' : 'Password'" />
            <button type="button" @click="generate()" class="text-[11px] font-semibold text-teal-700 hover:text-teal-800">
                Generate Password
            </button>
        </div>
        <x-text-input
            x-ref="password"
            id="password"
            name="password"
            type="password"
            autocomplete="{{ $isEdit ? 'new-password' : 'password' }}"
            class="block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500"
            @if(!$isEdit) required @endif
        />
        <x-input-error :messages="$errors->get('password')" />
    </div>

    <div class="space-y-2">
        <x-input-label for="password_confirmation" :value="$isEdit ? 'Konfirmasi Password Baru' : 'Konfirmasi Password'" />
        <x-text-input
            x-ref="password_confirmation"
            id="password_confirmation"
            name="password_confirmation"
            type="password"
            autocomplete="{{ $isEdit ? 'new-password' : 'password' }}"
            class="block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500"
            @if(!$isEdit) required @endif
        />
        <x-input-error :messages="$errors->get('password_confirmation')" />
    </div>
</div>
