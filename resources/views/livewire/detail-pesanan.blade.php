<div class="wrap pt-4">
    <x-breadcrumb :items="['Beranda' => route('home'), 'Pesanan saya' => route('pesanan.index'), $pesanan->nomor => null]" />

    <div class="mt-4 flex flex-wrap items-center gap-3">
        <h1 class="text-4xl tabular-nums md:text-5xl">{{ $pesanan->nomor }}</h1>
        <x-status-pesanan :status="$pesanan->status" class="text-sm" />
    </div>
    <p class="mt-1 text-ink-muted">Dibuat @tanggal($pesanan->created_at)</p>

    <x-flash class="mt-6" />

    <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_380px] lg:items-start">
        <div class="flex flex-col gap-6">
            @if ($pesanan->menungguPembayaran())
                <section class="card flex flex-col gap-4 border-warn/40 bg-warn-soft p-6 sm:flex-row sm:items-center">
                    <x-icon name="clock" class="size-6 shrink-0 text-warn" />
                    <div class="flex-1">
                        <h2 class="font-sans text-base font-medium tracking-normal">Selesaikan pembayaran sebelum @tanggal($pesanan->batas_bayar)</h2>
                        <p class="mt-0.5 text-sm text-ink-muted">Stok sudah dipesankan untukmu. Lewat dari batas itu, pesanan kedaluwarsa otomatis.</p>
                        @error('bayar') <p class="field-error" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <button type="button" wire:click="bayar" class="btn btn-primary" wire:loading.attr="disabled" wire:target="bayar">
                        <x-icon name="credit-card" /> Bayar sekarang
                    </button>
                </section>
            @endif

            <section class="card" aria-labelledby="judul-barang">
                <h2 id="judul-barang" class="border-b border-line px-6 py-5 text-xl">Isi pesanan</h2>
                <ul class="divide-y divide-line">
                    @foreach ($pesanan->items as $item)
                        <li wire:key="item-{{ $item->id }}" class="flex items-center gap-4 px-6 py-4">
                            @if ($item->fotoUrl())
                                <img src="{{ $item->fotoUrl() }}" alt="" class="size-14 shrink-0 rounded-xs bg-surface-muted object-cover">
                            @else
                                <x-foto-produk :foto="null" class="size-14 shrink-0 rounded-xs" />
                            @endif
                            <div class="min-w-0 flex-1 text-[15px]">
                                <p class="truncate font-medium">{{ $item->nama_produk }}</p>
                                <p class="text-sm text-ink-muted tabular-nums">{{ $item->nama_varian }} · {{ $item->jumlah }} × @rupiah($item->harga)</p>
                            </div>
                            <p class="price whitespace-nowrap">@rupiah($item->jumlahHarga())</p>
                        </li>
                    @endforeach
                </ul>
            </section>

            <section class="card p-6" aria-labelledby="judul-kirim">
                <h2 id="judul-kirim" class="flex items-center gap-2 text-xl"><x-icon name="map-pin" class="size-5 text-brand" /> Dikirim ke</h2>
                <div class="mt-3 text-[15px]">
                    <p class="font-medium">{{ $pesanan->alamat['penerima'] }} <span class="font-normal text-ink-muted">· {{ $pesanan->alamat['label'] }}</span></p>
                    <p class="text-ink-muted">{{ $pesanan->alamat['no_hp'] }}</p>
                    <p class="text-ink-muted">{{ $pesanan->alamat['alamat'] }}, {{ $pesanan->alamat['kode_pos'] }}</p>
                </div>
            </section>
        </div>

        <aside class="panel p-6 lg:sticky lg:top-36">
            <h2 class="text-lg">Rincian harga</h2>
            <dl class="mt-4 flex flex-col gap-2 text-[15px]">
                <div class="flex justify-between">
                    <dt class="text-ink-muted">Subtotal</dt>
                    <dd class="tabular-nums">@rupiah($pesanan->subtotal)</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-ink-muted">Ongkir</dt>
                    <dd class="tabular-nums">@rupiah($pesanan->ongkir)</dd>
                </div>
                @if ($pesanan->diskon)
                    <div class="flex justify-between">
                        <dt class="text-ink-muted">Diskon</dt>
                        <dd class="tabular-nums text-ok">−@rupiah($pesanan->diskon)</dd>
                    </div>
                @endif
                <div class="mt-2 flex items-baseline justify-between border-t border-line pt-3">
                    <dt class="font-medium">Total</dt>
                    <dd class="price text-[28px] leading-none">@rupiah($pesanan->total)</dd>
                </div>
                @if ($pesanan->dibayar_at)
                    <div class="mt-3 flex justify-between border-t border-line pt-3 text-sm">
                        <dt class="text-ink-muted">Dibayar</dt>
                        <dd>@tanggal($pesanan->dibayar_at){{ $pesanan->pembayaran?->metode ? ' · '.str($pesanan->pembayaran->metode)->replace('_', ' ')->title() : '' }}</dd>
                    </div>
                @endif
            </dl>
        </aside>
    </div>
</div>

@if ($skripJendela)
    @assets
        <script @foreach ($skripJendela as $atribut => $nilai) {{ $atribut }}="{{ $nilai }}" @endforeach></script>
    @endassets
@endif

@script
    <script>
        // Snap tampil sebagai jendela di atas halaman; tanpa skrip jendela (simulasi) pindah ke halaman pembayarannya
        const bukaPembayaran = ({ token, url }) => {
            if (!window.snap) {
                window.location.href = url;
                return;
            }
            window.snap.pay(token, {
                onSuccess: () => $wire.$refresh(),
                onPending: () => $wire.$refresh(),
            });
        };

        $wire.on('buka-pembayaran', bukaPembayaran);

        if ($wire.bukaOtomatis) {
            bukaPembayaran($wire.bukaOtomatis);
        }
    </script>
@endscript
