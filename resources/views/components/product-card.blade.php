@props(['barang', 'href', 'action' => 'Lihat detail'])

<a wire:navigate href="{{ $href }}"
    {{ $attributes->class('group card flex flex-col overflow-hidden transition hover:-translate-y-1 hover:shadow-lg hover:shadow-brand/10') }}>
    <div class="relative aspect-[4/3] overflow-hidden bg-surface-muted">
        @if ($barang->stok_barang < 1)
            <span class="badge badge-bad absolute top-3 left-3 z-10 shadow-sm">Stok habis</span>
        @endif
        <img src="{{ url('build/images') }}/{{ $barang->gambar }}" alt="{{ $barang->nama_barang }}" loading="lazy"
            class="size-full object-cover transition duration-500 group-hover:scale-105">
    </div>
    <div class="flex flex-1 flex-col gap-1 p-5">
        <h3 class="text-lg font-semibold">{{ $barang->nama_barang }}</h3>
        <p class="font-semibold text-brand">@rupiah($barang->harga_barang)</p>
        <span class="mt-auto inline-flex items-center gap-1 pt-3 text-sm font-medium text-ink-muted transition group-hover:text-brand">
            {{ $action }} <x-icon name="arrow-right" class="transition group-hover:translate-x-0.5" />
        </span>
    </div>
</a>
