@props(['title', 'subtitle' => null])

<div class="mx-auto flex max-w-md flex-col px-4 py-12 sm:py-16">
    <div class="card p-6 sm:p-8">
        <h1 class="text-3xl font-bold">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-2 text-ink-muted">{{ $subtitle }}</p>
        @endif
        <div class="mt-8">
            {{ $slot }}
        </div>
    </div>
    @isset($footer)
        <p class="mt-6 text-center text-sm text-ink-muted">{{ $footer }}</p>
    @endisset
</div>
