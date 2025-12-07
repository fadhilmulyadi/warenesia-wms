@php
    $roleValue = $item->role instanceof \BackedEnum ? $item->role->value : (string) $item->role;
    $statusValue = $item->status instanceof \BackedEnum ? $item->status->value : (string) $item->status;

    $roleLabel = $roles[$roleValue] ?? ucfirst(str_replace('_', ' ', $roleValue));
    $statusLabel = $statuses[$statusValue] 
        ?? ($item->status instanceof \BackedEnum && method_exists($item->status, 'label')
            ? $item->status->label()
            : ucfirst(str_replace('_', ' ', $statusValue)));
    $statusVariant = $statusVariants[$statusValue] ?? 'neutral';
@endphp

<x-mobile.card>
    <div class="flex items-center justify-between">
        <div class="font-semibold text-slate-900">{{ $item->name }}</div>
        <x-badge :variant="$statusVariant">{{ $statusLabel }}</x-badge>
    </div>

    <div class="text-xs text-slate-500">
        {{ $item->email }}
    </div>

    <div class="text-xs">
        <x-badge variant="teal">{{ $roleLabel }}</x-badge>
    </div>

    <div class="pt-2 flex items-center justify-between text-xs text-slate-500 border-t border-slate-50 mt-1">
        <span>Dibuat: {{ $item->created_at?->format('d M Y') }}</span>
        <span>Login: {{ $item->last_login_at?->format('d M Y H:i') ?? '-' }}</span>
    </div>

    <div class="pt-3 flex gap-2">
        <a href="{{ route('users.edit', $item) }}"
            class="flex-1 h-9 rounded-lg bg-slate-100 text-slate-700 text-xs flex items-center justify-center gap-2 hover:bg-slate-200 transition">
            <x-lucide-pencil class="w-4 h-4" /> Edit
        </a>

        @if(auth()->user()->can('delete', $item) && !isset($deletionGuards[$item->id]))
            <button x-on:click="$dispatch('open-delete-modal', { 
                            action: '{{ route('users.destroy', $item) }}',
                            title: 'Hapus User',
                            message: 'Yakin ingin menghapus user ini?',
                            itemName: '{{ addslashes($item->name) }}'
                        })"
                class="w-9 h-9 flex items-center justify-center rounded-lg bg-rose-100 text-rose-600 hover:bg-rose-200 transition">
                <x-lucide-trash class="w-4 h-4" />
            </button>
        @else
            <button disabled
                class="w-9 h-9 flex items-center justify-center rounded-lg bg-slate-50 text-slate-300 cursor-not-allowed">
                <x-lucide-ban class="w-4 h-4" />
            </button>
        @endif
    </div>
</x-mobile.card>
