@props([
    'name' => 'password',
    'label' => 'Password',
    'id' => null,
    'required' => false,
    'placeholder' => '',
    'autocomplete' => 'new-password',
])

@php
    $id = $id ?? $name;
@endphp

<div class="space-y-2" x-data="{ show: false }">
    <div class="flex items-center justify-between">
        <x-input-label :for="$id" :value="$label" />
    </div>

    <div class="relative">
        <x-text-input
            :id="$id"
            :name="$name"
            x-bind:type="show ? 'text' : 'password'"
            :required="$required"
            :placeholder="$placeholder"
            autocomplete="{{ $autocomplete }}"
            {{ $attributes->merge([
                'class' => 'block w-full h-[42px] rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-teal-500 focus:ring-teal-500 pr-10'
            ]) }}
        />

        <button
            type="button"
            @click="show = !show"
            class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 focus:outline-none"
        >
            <x-lucide-eye x-show="!show" class="w-4 h-4" />
            <x-lucide-eye-off x-show="show" x-cloak class="w-4 h-4" />
        </button>
    </div>

    <x-input-error :messages="$errors->get($name)" />
</div>

