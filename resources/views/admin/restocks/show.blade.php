@extends('layouts.app')

@section('title', 'Restock #' . $restock->po_number)

@section('page-header')
    <div class="flex flex-col">
        <h1 class="text-base font-semibold text-slate-900">
            Restock #{{ $restock->po_number }}
        </h1>
        <p class="text-xs text-slate-500">
            Supplier: {{ $restock->supplier->name ?? 'Unknown supplier' }}
        </p>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        @include('admin.components.status-badge', [
            'status' => $restock->status,
            'label' => $restock->status_label,
        ])

        <a
            href="{{ route('admin.restocks.index') }}"
            class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50"
        >
            Back to list
        </a>

        @if($restock->isPending())
            <form method="POST" action="{{ route('admin.restocks.cancel', $restock) }}" class="inline-flex">
                @csrf
                @method('PATCH')
                <button
                    type="submit"
                    class="inline-flex items-center rounded-lg bg-rose-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-600"
                >
                    Cancel order
                </button>
            </form>
        @elseif($restock->isConfirmed())
            <form method="POST" action="{{ route('admin.restocks.mark-in-transit', $restock) }}" class="inline-flex">
                @csrf
                @method('PATCH')
                <button
                    type="submit"
                    class="inline-flex items-center rounded-lg bg-sky-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-sky-700"
                >
                    Mark in transit
                </button>
            </form>
            <form method="POST" action="{{ route('admin.restocks.cancel', $restock) }}" class="inline-flex">
                @csrf
                @method('PATCH')
                <button
                    type="submit"
                    class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                >
                    Cancel
                </button>
            </form>
        @elseif($restock->isInTransit())
            <form method="POST" action="{{ route('admin.restocks.mark-received', $restock) }}" class="inline-flex">
                @csrf
                @method('PATCH')
                <button
                    type="submit"
                    class="inline-flex items-center rounded-lg bg-emerald-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-600"
                >
                    Mark as received
                </button>
            </form>
        @endif
    </div>
@endsection

@section('content')
    <div class="max-w-5xl mx-auto space-y-4 text-xs">
        @if($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-red-700">
                <div class="font-semibold mb-1">There are some issues:</div>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $errorMessage)
                        <li>{{ $errorMessage }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        {{-- Status timeline --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-4 space-y-3">
            <h2 class="text-[11px] font-semibold text-slate-800 uppercase tracking-wide">
                Status timeline
            </h2>
            @include('admin.components.restock-status-timeline', ['status' => $restock->status])
        </div>

        {{-- Meta restock --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-4 space-y-3">
            <h2 class="text-[11px] font-semibold text-slate-800 uppercase tracking-wide">
                Order info
            </h2>

            <dl class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="space-y-0.5">
                    <dt class="text-[11px] text-slate-500">PO number</dt>
                    <dd class="text-[13px] font-medium text-slate-900">
                        {{ $restock->po_number }}
                    </dd>
                </div>

                <div class="space-y-0.5">
                    <dt class="text-[11px] text-slate-500">Order date</dt>
                    <dd class="text-[13px] text-slate-900">
                        {{ optional($restock->order_date)->format('d M Y') ?? '-' }}
                    </dd>
                </div>

                <div class="space-y-0.5">
                    <dt class="text-[11px] text-slate-500">Expected delivery</dt>
                    <dd class="text-[13px] text-slate-900">
                        {{ optional($restock->expected_delivery_date)->format('d M Y') ?? '-' }}
                    </dd>
                </div>

                <div class="space-y-0.5">
                    <dt class="text-[11px] text-slate-500">Supplier</dt>
                    <dd class="text-[13px] text-slate-900">
                        {{ $restock->supplier->name ?? 'Unknown supplier' }}
                    </dd>
                </div>

                <div class="space-y-0.5">
                    <dt class="text-[11px] text-slate-500">Total items</dt>
                    <dd class="text-[13px] text-slate-900">
                        {{ number_format((int) $restock->total_items) }}
                    </dd>
                </div>

                <div class="space-y-0.5">
                    <dt class="text-[11px] text-slate-500">Total quantity</dt>
                    <dd class="text-[13px] text-slate-900">
                        {{ number_format((int) $restock->total_quantity) }}
                    </dd>
                </div>

                <div class="space-y-0.5">
                    <dt class="text-[11px] text-slate-500">Total amount</dt>
                    <dd class="text-[13px] text-slate-900">
                        Rp {{ number_format((float) $restock->total_amount, 2, ',', '.') }}
                    </dd>
                </div>
            </dl>

            @if($restock->notes)
                <div class="pt-2 border-t border-slate-100">
                    <dt class="text-[11px] text-slate-500 mb-1">Notes</dt>
                    <dd class="text-[12px] text-slate-800 whitespace-pre-line">
                        {{ $restock->notes }}
                    </dd>
                </div>
            @endif
        </div>

        {{-- Items --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-4 space-y-3">
            <div class="flex items-center justify-between mb-1">
                <h2 class="text-[11px] font-semibold text-slate-800 uppercase tracking-wide">
                    Items
                </h2>
            </div>

            <div class="rounded-xl border border-slate-200 overflow-hidden">
                <table class="min-w-full text-left text-xs">
                    <thead class="bg-slate-50 text-[11px] text-slate-500 uppercase tracking-wide">
                        <tr>
                            <th class="px-3 py-2">Product</th>
                            <th class="px-3 py-2 w-24 text-right">Quantity</th>
                            <th class="px-3 py-2 w-28 text-right">Unit cost</th>
                            <th class="px-3 py-2 w-28 text-right">Line total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($restock->items as $item)
                            <tr>
                                <td class="px-3 py-2">
                                    <div class="flex flex-col">
                                        <span class="text-[12px] font-medium text-slate-900">
                                            {{ $item->product->name ?? 'Unknown product' }}
                                        </span>
                                        <span class="text-[11px] text-slate-500">
                                            SKU: {{ $item->product->sku ?? 'N/A' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-right text-[12px] text-slate-900">
                                    {{ number_format((int) $item->quantity) }}
                                </td>
                                <td class="px-3 py-2 text-right text-[12px] text-slate-900">
                                    {{ number_format((float) $item->unit_cost, 2, ',', '.') }}
                                </td>
                                <td class="px-3 py-2 text-right text-[12px] text-slate-900">
                                    {{ number_format((float) $item->line_total, 2, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-4 text-center text-[11px] text-slate-500">
                                    No items recorded for this restock order.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Supplier rating --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-4 space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-[11px] font-semibold text-slate-800 uppercase tracking-wide">
                    Supplier rating
                </h2>
                @if($restock->hasRating())
                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">
                        Rated
                    </span>
                @endif
            </div>

            @if(! $restock->isReceived())
                <p class="text-[11px] text-slate-500">
                    Rating is available after this restock order is marked as received.
                </p>
            @elseif(! $restock->hasRating())
                <form method="POST" action="{{ route('admin.restocks.rate', $restock) }}" class="space-y-3">
                    @csrf
                    @method('PATCH')

                    <div class="space-y-1">
                        <label for="rating" class="text-[11px] font-semibold text-slate-700">
                            Rating ({{ \App\Models\RestockOrder::MIN_RATING }}-{{ \App\Models\RestockOrder::MAX_RATING }})
                        </label>
                        <select
                            id="rating"
                            name="rating"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-[12px]"
                        >
                            @for($i = \App\Models\RestockOrder::MIN_RATING; $i <= \App\Models\RestockOrder::MAX_RATING; $i++)
                                <option value="{{ $i }}" @selected((int) old('rating', $restock->rating) === $i)>
                                    {{ $i }}
                                </option>
                            @endfor
                        </select>
                        @error('rating')
                            <p class="text-[11px] text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1">
                        <label for="rating_notes" class="text-[11px] font-semibold text-slate-700">
                            Feedback (optional)
                        </label>
                        <textarea
                            id="rating_notes"
                            name="rating_notes"
                            rows="3"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-[12px]"
                            placeholder="Share brief feedback about supplier performance (delivery speed, accuracy, etc.)."
                        >{{ old('rating_notes', $restock->rating_notes) }}</textarea>
                        @error('rating_notes')
                            <p class="text-[11px] text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <button
                            type="submit"
                            class="inline-flex items-center rounded-lg bg-teal-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-teal-600"
                        >
                            Save rating
                        </button>
                        <a
                            href="{{ route('admin.restocks.show', $restock) }}"
                            class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                        >
                            Cancel
                        </a>
                    </div>
                </form>
            @else
                <div class="space-y-3">
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center gap-2">
                            <span class="text-lg font-semibold text-slate-900">
                                {{ $restock->rating }}/{{ \App\Models\RestockOrder::MAX_RATING }}
                            </span>
                            <div class="flex items-center gap-0.5">
                                @for($i = \App\Models\RestockOrder::MIN_RATING; $i <= \App\Models\RestockOrder::MAX_RATING; $i++)
                                    <x-lucide-star class="h-4 w-4 {{ $i <= (int) $restock->rating ? 'text-yellow-400' : 'text-slate-300' }}" />
                                @endfor
                            </div>
                        </div>
                        <div class="text-[11px] text-slate-500">
                            Rated by {{ $restock->ratingGivenBy->name ?? 'Unknown user' }}
                            @if($restock->rating_given_at)
                                on {{ $restock->rating_given_at->format('d M Y H:i') }}
                            @endif
                        </div>
                    </div>

                    @if($restock->rating_notes)
                        <div class="rounded-lg bg-slate-50 px-3 py-2 text-[12px] text-slate-800 whitespace-pre-line">
                            {{ $restock->rating_notes }}
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection
