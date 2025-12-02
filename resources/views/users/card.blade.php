@php
    $roleLabel = $roles[$item->role] ?? ucfirst($item->role);
    $statusLabel = $statuses[$item->status] ?? ucfirst($item->status);
    $statusVariant = $statusVariants[$item->status] ?? 'neutral';
@endphp

<div class="rounded-xl border border-slate-200 bg-white p-4 space-y-2">

    <div class="flex items-center justify-between">
        <div class="font-semibold text-slate-900">{{ $item->name }}</div>
        <x-badge :variant="$statusVariant">{{ $statusLabel }}</x-badge>
    </div>

    <div class="text-xs text-slate-500">
        {{ $item->email }}
    </div>

    <div class="text-xs">
        <x-badge variant="blue">{{ $roleLabel }}</x-badge>
    </div>

    <div class="pt-2 flex items-center justify-between text-xs text-slate-500">
        <span>Dibuat: {{ $item->created_at?->format('d M Y') }}</span>
        <span>Login: {{ $item->last_login_at?->format('d M Y H:i') }}</span>
    </div>

    <div class="pt-3 flex gap-2">
        <a href="{{ route('users.edit', $item) }}"
           class="flex-1 h-9 rounded-lg bg-slate-100 text-slate-700 text-xs flex items-center justify-center gap-2">
            <x-lucide-pencil class="w-4 h-4" /> Edit
        </a>

        <button
            x-on:click="$dispatch('open-delete-modal', { 
                action: '{{ route('users.destroy', $item) }}',
                title: 'Hapus User',
                message: 'Yakin ingin menghapus user ini?',
                itemName: '{{ addslashes($item->name) }}'
            })"
            class="w-9 h-9 flex items-center justify-center rounded-lg bg-rose-100 text-rose-600">
            <x-lucide-trash class="w-4 h-4" />
        </button>
    </div>

</div>
