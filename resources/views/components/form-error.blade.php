@props(['message' => null])

@php
    $serverMessage = $message ?: null;
@endphp

<div
    x-data="{
        serverMessage: @js($serverMessage),
        clientMessage: null,
        currentMessage() {
            return this.clientMessage || this.serverMessage;
        }
    }"
    x-effect="
        const raw = $el.getAttribute('message') ?? $el.getAttribute('data-message');
        clientMessage = raw && raw !== 'null' ? raw : null;
    "
    x-show="currentMessage()"
    x-cloak
    role="alert"
    {{ $attributes->merge(['class' => 'mt-1 text-[11px] text-red-600 flex items-center gap-1']) }}
>
    <x-lucide-alert-circle class="w-3 h-3" />
    <span x-text="currentMessage()">{{ $serverMessage }}</span>
</div>
