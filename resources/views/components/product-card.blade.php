{{-- Kartu produk di grid toko. Stok menipis diberi penanda champagne, stok habis diberi penanda gelap dan foto diredupkan. --}}
@props(['barang', 'href'])

@php($habis = $barang->habis())

<a wire:navigate href="{{ $href }}" {{ $attributes->class(['group flex flex-col']) }}>
    <div class="relative aspect-[4/5] overflow-hidden bg-surface-muted">
        <img src="{{ url('build/images') }}/{{ $barang->gambar }}" alt="{{ $barang->nama_barang }}" loading="lazy"
            @class(['size-full object-cover transition-transform duration-700 group-hover:scale-[1.03]', 'opacity-70 grayscale-[.85]' => $habis])>
        @if ($habis)
            <span class="badge badge-out absolute top-3 left-3">Habis</span>
        @elseif ($barang->menipis())
            <span class="badge badge-low absolute top-3 left-3">Tinggal {{ $barang->stok_barang }}</span>
        @endif
    </div>
    <h3 class="mt-4 text-xl leading-snug group-hover:underline group-hover:decoration-gold group-hover:decoration-1 group-hover:underline-offset-4">{{ $barang->nama_barang }}</h3>
    <p class="mt-1 flex items-baseline justify-between gap-2">
        <span class="price">@rupiah($barang->harga_barang)</span>
        <span class="text-[13px] text-ink-muted">{{ $habis ? 'Sedang tidak tersedia' : 'Stok '.$barang->stok_barang }}</span>
    </p>
</a>
