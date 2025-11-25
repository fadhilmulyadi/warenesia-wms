@php
    use App\Models\RestockOrder;

    $statusColors = [
        RestockOrder::STATUS_PENDING => 'bg-amber-50 text-amber-700 border-amber-200',
        RestockOrder::STATUS_CONFIRMED => 'bg-sky-50 text-sky-700 border-sky-200',
        RestockOrder::STATUS_IN_TRANSIT => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        RestockOrder::STATUS_RECEIVED => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        RestockOrder::STATUS_CANCELLED => 'bg-rose-50 text-rose-700 border-rose-200',
    ];

    $badgeClass = $statusColors[$status] ?? 'bg-slate-50 text-slate-700 border-slate-200';
    $badgeLabel = $label ?? (RestockOrder::statusOptions()[$status] ?? ucfirst((string) $status));
@endphp

<span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-semibold {{ $badgeClass }}">
    {{ $badgeLabel }}
</span>
