@props(['href' => null])

<tr 
    {{ $attributes->merge(['class' => 'group transition-colors border-b border-slate-100 last:border-b-0 ' . ($href ? 'hover:bg-slate-50 cursor-pointer' : '')]) }}
    @if($href) onclick="window.location.href='{{ $href }}'" @endif
>
    {{ $slot }}
</tr>