@props(['title', 'subtitle' => null])

<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <h1 class="text-3xl font-bold">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-1 text-ink-muted">{{ $subtitle }}</p>
        @endif
    </div>
    {{ $slot }}
</div>
