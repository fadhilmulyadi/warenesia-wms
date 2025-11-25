@props(['href' => '#', 'title' => 'Tambah Baru'])

<a 
    href="{{ $href }}"
    {{ $attributes->merge([
        'class' =>
            'inline-flex items-center justify-center 
             h-[42px] px-3 shrink-0 rounded-xl 
             border border-slate-300 bg-white 
             text-slate-600 hover:bg-slate-50 hover:border-teal-500 hover:text-teal-600
             transition-all duration-200 shadow-sm'
    ]) }}
    title="{{ $title }}"
    @click.stop
>
    <x-lucide-plus class="w-4 h-4" />
</a>
