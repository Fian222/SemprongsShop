<div class="wrap pt-4">
    <x-breadcrumb :items="['Beranda' => route('home'), 'Riwayat pesanan' => null]" />

    <h1 class="mt-4 text-4xl md:text-5xl">Riwayat pesanan</h1>

    <x-flash class="mt-6" />

    <div class="mt-6 flex max-w-3xl flex-col gap-4">
        @forelse ($pesanan as $item)
            <article wire:key="pesanan-{{ $item->id }}" class="card">
                <header class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-5 py-4">
                    <div>
                        <h2 class="text-base tracking-normal">Pesanan {{ $item->kode_pemesanan }}</h2>
                        <p class="text-sm text-ink-muted">@tanggal($item->created_at)</p>
                    </div>
                    <x-status-pesanan :status="$item->status" />
                </header>

                <ul class="flex flex-col gap-3 px-5 py-4">
                    @foreach ($item->pesanandetail as $pesan)
                        <li class="flex items-center gap-3">
                            <img src="{{ url('build/images') }}/{{ $pesan->barang->gambar }}" alt=""
                                class="size-12 rounded-xs bg-surface-muted object-cover">
                            <span class="flex-1 text-[15px]">{{ $pesan->barang->nama_barang }}</span>
                            <span class="text-sm text-ink-muted tabular-nums">× {{ $pesan->jumlah_pesanan }}</span>
                        </li>
                    @endforeach
                </ul>

                <footer class="flex items-center justify-between border-t border-line px-5 py-4">
                    <span class="text-sm text-ink-muted">Total</span>
                    <span class="price text-lg">@rupiah($item->total_harga)</span>
                </footer>
            </article>
        @empty
            <div class="card">
                <x-empty icon="history">
                    Belum ada pesanan yang di-checkout. Pesanan yang sudah kamu konfirmasi akan muncul di sini.
                    <x-slot:action>
                        <a wire:navigate href="{{ route('produk') }}" class="btn btn-primary">Mulai belanja</a>
                    </x-slot:action>
                </x-empty>
            </div>
        @endforelse
    </div>
</div>
