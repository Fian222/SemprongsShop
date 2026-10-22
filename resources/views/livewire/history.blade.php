<div class="mx-auto max-w-6xl px-4 pt-8 sm:px-6">
    <x-breadcrumb :items="['Beranda' => route('home'), 'Riwayat' => null]" />

    <h1 class="mt-6 text-4xl font-bold">Riwayat pesanan</h1>

    <x-flash class="mt-6" />

    <div class="mt-8 flex flex-col gap-4">
        @forelse ($pesanan as $item)
            <article wire:key="pesanan-{{ $item->id }}" class="card p-5 sm:p-6">
                <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line pb-4">
                    <div>
                        <p class="font-mono text-sm font-semibold">{{ $item->kode_pemesanan }}</p>
                        <p class="text-sm text-ink-muted">@tanggal($item->created_at)</p>
                    </div>
                    <x-status-pesanan :status="$item->status" />
                </header>

                <ul class="flex flex-col gap-3 py-4">
                    @foreach ($item->pesanandetail as $pesan)
                        <li class="flex items-center gap-3">
                            <img src="{{ url('build/images') }}/{{ $pesan->barang->gambar }}" alt=""
                                class="size-12 rounded-lg object-cover">
                            <span class="flex-1 text-sm font-medium">{{ $pesan->barang->nama_barang }}</span>
                            <span class="text-sm text-ink-muted">× {{ $pesan->jumlah_pesanan }}</span>
                        </li>
                    @endforeach
                </ul>

                <footer class="flex items-center justify-between border-t border-line pt-4">
                    <span class="text-sm text-ink-muted">Total</span>
                    <span class="text-lg font-bold">@rupiah($item->total_harga)</span>
                </footer>
            </article>
        @empty
            <div class="card">
                <x-empty icon="history">Belum ada pesanan yang di-checkout.</x-empty>
            </div>
        @endforelse
    </div>
</div>
