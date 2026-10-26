@props(['type' => 'success'])

@php
    $gaya = $type === 'success' ? 'bg-ok-soft text-ok' : 'bg-bad-soft text-bad';
@endphp

<div role="status" {{ $attributes->merge(['class' => "flex items-start gap-3 rounded-xs px-4 py-3 text-sm font-medium $gaya"]) }}>
    <x-icon :name="$type === 'success' ? 'circle-check' : 'circle-alert'" class="mt-px size-5" />
    <div>{{ $slot }}</div>
</div>
