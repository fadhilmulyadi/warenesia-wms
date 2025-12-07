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
    $textColor = 'text-gray-900';
@endphp

<div class="w-full py-6">
    <ol class="items-center sm:flex w-full">

        @foreach ($steps as $stepKey => $stepData)
            @php
                $index = array_search($stepKey, array_keys($steps), true);
                $isCompleted = !$isCancelled && $index < $currentIndex;
                $isCurrent = !$isCancelled && $index === $currentIndex;

                if ($isCancelled) {
                    $dotColor = 'bg-red-100 text-red-700';
                    $ringColor = 'sm:ring-8 sm:ring-red-200';
                    $lineColor = 'bg-red-200';
                    $textColor = 'text-red-700';
                } elseif ($index < $currentIndex || ($isCurrent && $stepKey === RestockOrder::STATUS_RECEIVED)) {
                    $dotColor = 'bg-teal-600 text-white';
                    $ringColor = 'sm:ring-8 sm:ring-teal-300';
                    $lineColor = 'bg-teal-600';
                    $textColor = 'text-teal-700';
                } elseif ($isCurrent) {
                    $dotColor = 'bg-white text-teal-600 border border-teal-600';
                    $ringColor = 'sm:ring-8 sm:ring-teal-100';
                    $lineColor = 'bg-gray-200';
                    $textColor = 'text-teal-700';
                } else {
                    $dotColor = 'bg-gray-200 text-gray-500 border border-gray-300';
                    $ringColor = 'sm:ring-8 sm:ring-gray-100';
                    $lineColor = 'bg-gray-200';
                    $textColor = 'text-gray-500';
                }
            @endphp

            <li class="relative mb-8 sm:mb-0 flex-1">

                @if (!$loop->last)
                    <div class="hidden sm:block absolute top-3 left-1/2 w-full h-1 -translate-y-1/2 {{ $lineColor }} z-0"></div>
                @endif

                <div class="relative z-10 flex flex-col items-center">

                    {{-- Dot --}}
                    <div
                        class="flex items-center justify-center w-6 h-6 rounded-full {{ $dotColor }} {{ $ringColor }} shrink-0">
                        @if ($isCompleted || ($isCurrent && $stepKey === RestockOrder::STATUS_RECEIVED))
                            <x-lucide-check class="w-3 h-3" />
                        @elseif ($isCancelled)
                            <x-lucide-x class="w-3 h-3" />
                        @else
                            <x-dynamic-component :component="'lucide-' . $stepData['icon']" class="w-3 h-3" />
                        @endif
                    </div>
                </div>

                <div class="mt-3 sm:pe-0 text-center w-full">
                    <h3 class="text-base font-semibold {{ $textColor }} my-2">
                        {{ $stepData['label'] }}
                    </h3>
                </div>
            </li>
        @endforeach
    </ol>

    @if ($isCancelled)
        <div
            class="mt-8 mx-auto max-w-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center gap-3 shadow-sm">
            <x-lucide-alert-circle class="w-5 h-5" />
            <div class="text-sm font-medium">Order Cancelled</div>
        </div>
    @endif
</div>
