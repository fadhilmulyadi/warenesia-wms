<div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
    <table {{ $attributes->merge(['class' => 'min-w-full text-xs']) }}>
        {{ $slot }}
    </table>
</div>