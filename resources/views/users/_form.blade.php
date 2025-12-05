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

{{-- SECTION: General --}}
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

{{-- SECTION: Password --}}
{{-- SECTION: Password --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
    <x-password-generator 
        name="password" 
        :label="$isEdit ? 'Reset Password (opsional)' : 'Password'" 
        :required="!$isEdit" 
        :placeholder="$isEdit ? 'Kosongkan jika tidak ingin mengubah' : ''"
    />

    <div class="space-y-2">
        <x-input-label for="password_confirmation" :value="$isEdit ? 'Konfirmasi Password Baru' : 'Konfirmasi Password'" />
        <x-text-input
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
