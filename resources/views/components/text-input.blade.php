@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge([
    'class' => 'w-full h-[42px] rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-teal-500 focus:ring-teal-500 disabled:bg-slate-100 disabled:text-slate-500'
]) }}>
