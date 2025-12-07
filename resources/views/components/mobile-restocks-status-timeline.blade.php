@php
    use App\Models\RestockOrder;

    $steps = [
        RestockOrder::STATUS_PENDING => ['label' => 'Pending', 'icon' => 'clock'],
        RestockOrder::STATUS_CONFIRMED => ['label' => 'Confirmed', 'icon' => 'check-circle'],
        RestockOrder::STATUS_IN_TRANSIT => ['label' => 'In Transit', 'icon' => 'truck'],
        RestockOrder::STATUS_RECEIVED => ['label' => 'Received', 'icon' => 'package-check'],
    ];

    $statusValue = $status instanceof \BackedEnum ? $status->value : $status;

    $isCancelled = $statusValue === RestockOrder::STATUS_CANCELLED;
    $currentIndex = $isCancelled ? -1 : array_search($statusValue, array_keys($steps), true);
@endphp

<div class="w-full py-4 px-2">
    <ol class="relative border-s border-gray-200">
        @foreach ($steps as $stepKey => $stepData)
            @php
                $index = array_search($stepKey, array_keys($steps), true);
                $isCompleted = !$isCancelled && $index < $currentIndex;
                $isCurrent = !$isCancelled && $index === $currentIndex;

                // Determine colors based on state
                if ($isCancelled) {
                    $dotClass = 'bg-red-100 ring-red-50';
                    $iconClass = 'text-red-600';
                    $textClass = 'text-red-600';
                    $borderClass = 'border-red-200 bg-red-50 text-red-700';
                } elseif ($isCompleted) {
                    $dotClass = 'bg-teal-100 ring-teal-50';
                    $iconClass = 'text-teal-600';
                    $textClass = 'text-teal-700';
                    $borderClass = 'border-teal-200 bg-teal-50 text-teal-700';
                } elseif ($isCurrent) {
                    $dotClass = 'bg-teal-100 ring-teal-50';
                    $iconClass = 'text-teal-600';
                    $textClass = 'text-teal-700';
                    $borderClass = 'border-teal-200 bg-teal-50 text-teal-700';
                } else {
                    $dotClass = 'bg-gray-100 ring-gray-50';
                    $iconClass = 'text-gray-500';
                    $textClass = 'text-gray-500';
                    $borderClass = 'border-gray-200 bg-gray-50 text-gray-600';
                }
            @endphp

            <li class="mb-10 ms-6 last:mb-0">
                <span
                    class="absolute flex items-center justify-center w-6 h-6 rounded-full -start-3 ring-8 {{ $dotClass }}">
                    @if ($isCompleted || ($isCurrent && $stepKey === RestockOrder::STATUS_RECEIVED))
                        <x-lucide-check class="w-3 h-3 {{ $iconClass }}" />
                    @elseif ($isCancelled)
                        <x-lucide-x class="w-3 h-3 {{ $iconClass }}" />
                    @else
                        <x-dynamic-component :component="'lucide-' . $stepData['icon']" class="w-3 h-3 {{ $iconClass }}" />
                    @endif
                </span>

                <h3 class="flex items-center mb-1 text-sm font-semibold {{ $textClass }}">
                    {{ $stepData['label'] }}
                </h3>

            </li>
        @endforeach
    </ol>

    @if ($isCancelled)
        <div
            class="mt-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center gap-3 shadow-sm">
            <x-lucide-alert-circle class="w-5 h-5" />
            <div class="text-sm font-medium">Order Cancelled</div>
        </div>
    @endif
</div>
