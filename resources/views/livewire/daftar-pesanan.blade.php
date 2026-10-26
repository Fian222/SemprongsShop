<div class="wrap pt-4">
    <x-breadcrumb :items="['Beranda' => route('home'), 'Pesanan saya' => null]" />

    <h1 class="mt-4 text-4xl md:text-5xl">Pesanan saya</h1>

    <div class="mt-6 flex max-w-3xl flex-col gap-4">
        @forelse ($pesanans as $pesanan)
            <a wire:key="pesanan-{{ $pesanan->id }}" wire:navigate href="{{ route('pesanan.detail', $pesanan) }}"
                class="card block transition-colors hover:border-line-strong">
                <header class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-5 py-4">
                    <div>
                        <h2 class="font-sans text-base font-medium tracking-normal tabular-nums">{{ $pesanan->nomor }}</h2>
                        <p class="text-sm text-ink-muted">@tanggal($pesanan->created_at)</p>
                    </div>
                    <x-status-pesanan :status="$pesanan->status" />
                </header>
                <ul class="flex flex-col gap-3 px-5 py-4">
                    @foreach ($pesanan->items as $item)
                        <li class="flex items-center gap-3 text-[15px]">
                            <span class="flex-1">{{ $item->nama_produk }} <span class="text-ink-muted">· {{ $item->nama_varian }}</span></span>
                            <span class="text-sm text-ink-muted tabular-nums">× {{ $item->jumlah }}</span>
                        </li>
                    @endforeach
                </ul>
                <footer class="flex items-center justify-between border-t border-line px-5 py-4">
                    @if ($pesanan->menungguPembayaran())
                        <span class="text-sm text-warn">Bayar sebelum @tanggal($pesanan->batas_bayar)</span>
                    @else
                        <span class="text-sm text-ink-muted">Total</span>
                    @endif
                    <span class="price text-lg">@rupiah($pesanan->total)</span>
                </footer>
            </a>
        @empty
            <div class="card">
                <x-empty icon="history">
                    Belum ada pesanan. Pesanan yang kamu buat di checkout akan muncul di sini.
                    <x-slot:action>
                        <a wire:navigate href="{{ route('produk') }}" class="btn btn-primary">Mulai belanja</a>
                    </x-slot:action>
                </x-empty>
            </div>
        @endforelse
    </div>

    <div class="mt-8 max-w-3xl">
        {{ $pesanans->links() }}
    </div>
</div>
