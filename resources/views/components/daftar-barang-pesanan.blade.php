{{-- Daftar barang di dalam satu pesanan, untuk tabel admin --}}
@props(['pesanan'])

<ul class="flex flex-col gap-1.5">
    @foreach ($pesanan->items as $item)
        <li class="flex items-center gap-2.5">
            <span class="whitespace-nowrap">{{ $item->nama_produk }} <span class="text-ink-muted">· {{ $item->nama_varian }}</span></span>
            <span class="whitespace-nowrap text-ink-muted tabular-nums">× {{ $item->jumlah }}</span>
        </li>
    @endforeach
</ul>
