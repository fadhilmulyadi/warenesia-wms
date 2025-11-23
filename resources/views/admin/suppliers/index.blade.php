@extends('layouts.app')

@section('title', 'Suppliers')

@section('page-header')
    <div class="flex flex-col">
        <h1 class="text-base font-semibold text-slate-900">Suppliers</h1>
        <p class="text-xs text-slate-500">
            Manage suppliers used in products, purchases, and restock orders.
        </p>
    </div>

    <div class="flex items-center gap-2">
        <a
            href="{{ route('admin.suppliers.create') }}"
            class="inline-flex items-center rounded-lg bg-teal-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-teal-600"
        >
            <x-lucide-plus class="h-3 w-3 mr-1" />
            Add supplier
        </a>
    </div>
@endsection

@section('content')
    <div class="max-w-6xl mx-auto space-y-4 text-xs">
        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-red-800">
                {{ session('error') }}
            </div>
        @endif

        <form method="GET" action="{{ route('admin.suppliers.index') }}" class="flex items-center gap-2">
            <input
                type="text"
                name="q"
                value="{{ $search }}"
                placeholder="Search by name, contact, email, or phone..."
                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-[11px]"
            >
            <button
                type="submit"
                class="rounded-lg border border-slate-200 px-3 py-2 text-[11px] text-slate-700 hover:bg-slate-50"
            >
                Search
            </button>
        </form>

        <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
            <table class="min-w-full text-left text-xs">
                <thead class="bg-slate-50 text-[11px] text-slate-500 uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-2">Name</th>
                        <th class="px-4 py-2">Contact</th>
                        <th class="px-4 py-2">Email</th>
                        <th class="px-4 py-2">Phone</th>
                        <th class="px-4 py-2">Avg rating</th>
                        <th class="px-4 py-2">Rated restocks</th>
                        <th class="px-4 py-2 text-center">Status</th>
                        <th class="px-4 py-2 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($suppliers as $supplier)
                        <tr>
                            <td class="px-4 py-2">
                                <div class="flex flex-col">
                                    <span class="font-semibold text-slate-900">
                                        {{ $supplier->name }}
                                    </span>
                                    @if($supplier->city || $supplier->country)
                                        <span class="text-[10px] text-slate-500">
                                            {{ $supplier->city ? $supplier->city . ', ' : '' }}{{ $supplier->country }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-2">
                                <div class="flex flex-col">
                                    <span>{{ $supplier->contact_person ?: '-' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-2">
                                {{ $supplier->email ?: '-' }}
                            </td>
                            <td class="px-4 py-2">
                                {{ $supplier->phone ?: '-' }}
                            </td>
                            <td class="px-4 py-2">
                                @if($supplier->average_rating !== null)
                                    <div class="inline-flex items-center gap-1">
                                        <span class="text-[12px] text-slate-900">{{ number_format((float) $supplier->average_rating, 1) }}</span>
                                        <x-lucide-star class="h-3 w-3 text-yellow-400" />
                                    </div>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-2">
                                {{ $supplier->rated_restock_count ?? 0 }}
                            </td>
                            <td class="px-4 py-2 text-center">
                                @if($supplier->is_active)
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <a
                                        href="{{ route('admin.suppliers.edit', $supplier) }}"
                                        class="rounded-lg border border-slate-200 px-2 py-1 text-[11px] text-slate-700 hover:bg-slate-50"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.suppliers.destroy', $supplier) }}"
                                        onsubmit="return confirm('Delete this supplier? This action cannot be undone.');"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="rounded-lg border border-red-200 px-2 py-1 text-[11px] text-red-700 hover:bg-red-50"
                                        >
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-6 text-center text-[11px] text-slate-500">
                                No suppliers found. Try changing the filter or add a new supplier.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($suppliers->hasPages())
                <div class="border-t border-slate-100 px-4 py-2 flex items-center justify-between text-[11px] text-slate-500">
                    <div>
                        Showing
                        <span class="font-semibold text-slate-700">{{ $suppliers->firstItem() }}</span>
                        to
                        <span class="font-semibold text-slate-700">{{ $suppliers->lastItem() }}</span>
                        of
                        <span class="font-semibold text-slate-700">{{ $suppliers->total() }}</span>
                        suppliers
                    </div>
                    <div>
                        {{ $suppliers->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
