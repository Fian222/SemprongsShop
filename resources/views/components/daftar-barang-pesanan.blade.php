{{-- Daftar barang di dalam satu pesanan, untuk tabel admin --}}
@props(['pesanan'])

<ul class="flex flex-col gap-2">
    @foreach ($pesanan->pesanandetail as $pesan)
        <li class="flex items-center gap-2.5">
            <x-foto-produk :foto="$pesan->varian->produk->fotoUtama" class="size-9 rounded-xs bg-surface-muted" />
            <span class="whitespace-nowrap">{{ $pesan->varian->produk->nama }} <span class="text-ink-muted">· {{ $pesan->varian->nama }}</span></span>
            <span class="text-ink-muted tabular-nums">× {{ $pesan->jumlah_pesanan }}</span>
        </li>
    @endforeach
</ul>
