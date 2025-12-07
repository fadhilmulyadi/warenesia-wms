@props(['supplier'])

<div class="flex items-center justify-end gap-2">
    @can('update', $supplier)
        <a href="{{ route('suppliers.edit', $supplier) }}"
            class="inline-flex items-center justify-center w-8 h-8 text-slate-400 hover:text-teal-600 hover:bg-teal-50 rounded-lg transition-colors"
            title="Edit Supplier">
            <x-lucide-pencil class="w-4 h-4" />
        </a>
    @endcan
</div>