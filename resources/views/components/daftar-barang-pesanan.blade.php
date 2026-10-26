{{-- Daftar barang di dalam satu pesanan, untuk tabel admin --}}
@props(['pesanan'])

<ul class="flex flex-col gap-2">
    @foreach ($pesanan->pesanandetail as $pesan)
        <li class="flex items-center gap-2.5">
            <img src="{{ url('build/images') }}/{{ $pesan->barang->gambar }}" alt="" class="size-9 rounded-xs bg-surface-muted object-cover">
            <span class="whitespace-nowrap">{{ $pesan->barang->nama_barang }}</span>
            <span class="text-ink-muted tabular-nums">× {{ $pesan->jumlah_pesanan }}</span>
        </li>
    @endforeach
</ul>
