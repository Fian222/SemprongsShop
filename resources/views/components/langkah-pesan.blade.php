{{-- Posisi pembeli di alur pesan: 1 = tas belanja, 2 = checkout, 3 = pembayaran --}}
@props(['aktif'])

@php($langkah = ['Tas belanja', 'Checkout', 'Pembayaran'])

<ol aria-label="Langkah pemesanan" class="flex items-center gap-2 text-sm">
    @foreach ($langkah as $label)
        @php($ke = $loop->iteration)
        <li class="flex items-center gap-2" @if ($ke === $aktif) aria-current="step" @endif>
            <span @class([
                'grid size-6 place-items-center rounded-full text-xs font-medium',
                'bg-ink text-canvas' => $ke <= $aktif,
                'border border-line text-ink-muted' => $ke > $aktif,
            ])>
                @if ($ke < $aktif) <x-icon name="check" class="size-3.5" /> @else {{ $ke }} @endif
            </span>
            <span @class(['font-medium text-ink' => $ke === $aktif, 'text-ink-muted' => $ke !== $aktif, 'hidden sm:inline' => $ke !== $aktif])>{{ $label }}</span>
        </li>
        @unless ($loop->last)
            <li aria-hidden="true" class="h-px w-6 bg-line sm:w-10"></li>
        @endunless
    @endforeach
</ol>
