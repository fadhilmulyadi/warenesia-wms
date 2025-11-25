@php
    use App\Models\RestockOrder;

    $steps = [
        RestockOrder::STATUS_PENDING => 'Pending',
        RestockOrder::STATUS_CONFIRMED => 'Confirmed',
        RestockOrder::STATUS_IN_TRANSIT => 'In transit',
        RestockOrder::STATUS_RECEIVED => 'Received',
    ];

    $isCancelled = $status === RestockOrder::STATUS_CANCELLED;
    $currentIndex = $isCancelled
        ? -1
        : array_search($status, array_keys($steps), true);
@endphp

<div class="flex flex-col gap-3 text-xs">
    <div class="flex items-center justify-between gap-4">
        @foreach($steps as $stepKey => $stepLabel)
            @php
                $index = array_search($stepKey, array_keys($steps), true);
                $isDone = $currentIndex !== false && $index <= $currentIndex;
                $isCurrent = $currentIndex === $index;
            @endphp
            <div class="flex-1 flex items-center gap-2">
                <div class="relative flex items-center">
                    <div class="h-4 w-4 rounded-full {{ $isDone ? 'bg-teal-500' : 'bg-slate-200' }}"></div>
                    @if(!$loop->last)
                        <div class="absolute left-4 right-0 h-[2px] {{ $isDone ? 'bg-teal-500' : 'bg-slate-200' }}"></div>
                    @endif
                </div>
                <div class="flex flex-col">
                    <span class="text-[11px] font-semibold {{ $isDone ? 'text-slate-900' : 'text-slate-500' }}">
                        {{ $stepLabel }}
                    </span>
                    @if($isCurrent)
                        <span class="text-[10px] text-teal-600 font-medium">Current</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    @if($isCancelled)
        <div class="flex items-center gap-2 text-[11px] font-semibold text-rose-700">
            <x-lucide-ban class="h-3 w-3" />
            <span>Order cancelled</span>
        </div>
    @endif
</div>
