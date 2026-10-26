{{-- Kartu produk di grid toko. Butuh kolom dari scope Produk::untukKartu(). Produk yang semua variannya habis diredupkan. --}}
@props(['produk'])

@php($habis = $produk->habis())

<a wire:navigate href="{{ route('produk.detail', $produk->slug) }}" {{ $attributes->class(['group flex flex-col']) }}>
    <div class="relative aspect-[4/5] overflow-hidden bg-surface-muted">
        <x-foto-produk :foto="$produk->fotoUtama" :alt="$produk->nama" loading="lazy"
            @class(['size-full transition-transform duration-700 group-hover:scale-[1.03]', 'opacity-70 grayscale-[.85]' => $habis]) />
        @if ($habis)
            <span class="badge badge-out absolute top-3 left-3">Habis</span>
        @elseif ($produk->unggulan)
            <span class="badge badge-low absolute top-3 left-3">Unggulan</span>
        @endif
    </div>
    <h3 class="mt-4 text-xl leading-snug group-hover:underline group-hover:decoration-gold group-hover:decoration-1 group-hover:underline-offset-4">{{ $produk->nama }}</h3>
    <p class="mt-1 flex items-baseline justify-between gap-2">
        <span class="price">mulai @rupiah($produk->harga_mulai)</span>
        @if ($habis)
            <span class="text-[13px] text-ink-muted">Sedang tidak tersedia</span>
        @endif
    </p>
</a>
