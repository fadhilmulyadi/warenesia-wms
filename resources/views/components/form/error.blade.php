@props(['message' => null])

<p role="alert" {{ $attributes->merge(['class' => 'mt-1 text-xs text-rose-600']) }}>
    {{ $message }}
</p>
