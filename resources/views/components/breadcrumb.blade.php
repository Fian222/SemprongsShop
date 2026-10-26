{{-- $items: [label => url]; item terakhir (url null) adalah halaman aktif --}}
@props(['items'])

<nav aria-label="Breadcrumb" class="text-sm text-ink-muted">
    <ol class="flex flex-wrap items-center gap-1.5">
        @foreach ($items as $label => $url)
            <li class="flex items-center gap-1.5">
                @if ($url)
                    <a wire:navigate href="{{ $url }}" class="hover:text-ink hover:underline">{{ $label }}</a>
                    <x-icon name="chevron-right" class="size-3.5" />
                @else
                    <span aria-current="page" class="font-medium text-ink">{{ $label }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
