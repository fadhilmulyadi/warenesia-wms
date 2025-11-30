@props(['message' => null])

@php
    $serverMessage = $message ?: null;
@endphp

@php
    $outerKeys = ['message', 'data-message', 'x-bind:message', 'x-bind:data-message'];
    $outerAttributes = $attributes->only($outerKeys);
    $innerAttributes = $attributes->except($outerKeys);
@endphp

<div {{ $outerAttributes }}>
    <div
        x-data="{
            serverMessage: @js($serverMessage),
            clientMessage: null,
            currentMessage() {
                return this.clientMessage || this.serverMessage;
            },
            syncFromHost() {
                const host = this.$el.parentElement;
                if (! host) return;

                const raw = host.getAttribute('message') ?? host.getAttribute('data-message');
                this.clientMessage = raw && raw !== 'null' ? raw : null;
            }
        }"
        x-init="
            syncFromHost();

            const host = $el.parentElement;
            if (! host) return;

            const observer = new MutationObserver(() => syncFromHost());
            observer.observe(host, { attributes: true, attributeFilter: ['message', 'data-message'] });
        "
        x-show="currentMessage()"
        x-cloak
        role="alert"
        {{ $innerAttributes->merge(['class' => 'mt-1 text-[11px] text-red-600 flex items-center gap-1']) }}
    >
        <x-lucide-alert-circle class="w-3 h-3" />
        <span x-text="currentMessage()">{{ $serverMessage }}</span>
    </div>
</div>
