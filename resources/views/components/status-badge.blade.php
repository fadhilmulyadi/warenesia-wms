@props(['status', 'label' => null])

@php
    use App\Models\RestockOrder;
    use App\Models\IncomingTransaction;
    use App\Models\OutgoingTransaction;

    $colors = [
        'pending'    => 'bg-amber-50 text-amber-700 border-amber-200',
        'approved'   => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'completed'  => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'verified'   => 'bg-sky-50 text-sky-700 border-sky-200',
        'rejected'   => 'bg-rose-50 text-rose-700 border-rose-200',
        'cancelled'  => 'bg-slate-100 text-slate-700 border-slate-300',
        'shipped'    => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        'in_transit' => 'bg-indigo-50 text-indigo-700 border-indigo-200',

        RestockOrder::STATUS_PENDING    => 'bg-amber-50 text-amber-700 border-amber-200',
        RestockOrder::STATUS_CONFIRMED  => 'bg-sky-50 text-sky-700 border-sky-200',
        RestockOrder::STATUS_IN_TRANSIT => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        RestockOrder::STATUS_RECEIVED   => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        RestockOrder::STATUS_CANCELLED  => 'bg-rose-50 text-rose-700 border-rose-200',
    ];

    $color = $colors[$status] ?? 'bg-slate-50 text-slate-700 border-slate-200';

    $badgeLabel = $label ?? ucfirst(str_replace('_', ' ', (string) $status));
@endphp

<span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-semibold {{ $color }}">
    {{ $badgeLabel }}
</span>
