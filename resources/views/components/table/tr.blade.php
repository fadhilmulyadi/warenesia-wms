@props(['href' => null])

<tr 
    {{ $attributes->merge(['class' => 'group transition-colors border-b border-slate-100 last:border-b-0 ' . ($href ? 'hover:bg-slate-50 cursor-pointer' : '')]) }}
    @if($href)
        onclick="const interactiveSelector = 'a, button, input, select, textarea, label, [role=button], [data-prevent-row-navigation]'; if (event.target.closest(interactiveSelector)) return; window.location.href='{{ $href }}';"
    @endif
>
    {{ $slot }}
</tr>
