@props(['type' => 'success'])

@php
    $gaya = $type === 'success' ? 'border-ok/30 bg-ok-soft text-ok' : 'border-bad/30 bg-bad-soft text-bad';
@endphp

<div role="status" {{ $attributes->merge(['class' => "flex items-start gap-3 rounded-xl border px-4 py-3 text-sm font-medium $gaya"]) }}>
    <x-icon :name="$type === 'success' ? 'circle-check' : 'circle-alert'" class="mt-0.5 size-5" />
    <div>{{ $slot }}</div>
</div>
