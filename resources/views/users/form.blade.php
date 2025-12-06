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
    <x-password-input
        name="password"
        id="password"
        :label="$isEdit ? 'Reset Password (opsional)' : 'Password'"
        :required="!$isEdit"
        placeholder="{{ $isEdit ? 'Kosongkan jika tidak ingin mengubah' : '' }}"
    />

    <x-password-input
        name="password_confirmation"
        id="password_confirmation"
        :label="$isEdit ? 'Konfirmasi Password Baru' : 'Konfirmasi Password'"
        :required="!$isEdit"
    />
</div>
