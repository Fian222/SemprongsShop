@props(['title', 'subtitle' => null])

<div class="wrap flex justify-center py-12 sm:py-16">
    <div class="w-full max-w-md">
        <div class="card p-6 sm:p-8">
            <h1 class="text-4xl leading-tight">{{ $title }}</h1>
            @if ($subtitle)
                <p class="mt-2 text-ink-muted">{{ $subtitle }}</p>
            @endif
            <div class="mt-7">
                {{ $slot }}
            </div>
        </div>
        @isset($footer)
            <p class="mt-6 text-center text-sm text-ink-muted">{{ $footer }}</p>
        @endisset
    </div>
</div>
