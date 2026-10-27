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

            @if ($pesanan->pengiriman?->resi)
                <x-pelacakan :pengiriman="$pesanan->pengiriman" :pelacakan="$pelacakan" :gagal="$pelacakanGagal" />
            @endif

            <x-isi-pesanan :pesanan="$pesanan" />

            <section class="card p-6" aria-labelledby="judul-kirim">
                <h2 id="judul-kirim" class="flex items-center gap-2 text-xl"><x-icon name="map-pin" class="size-5 text-brand" /> Dikirim ke</h2>
                <x-alamat-pesanan :pesanan="$pesanan" class="mt-3" />
                @if ($pesanan->pengiriman)
                    <x-kurir-pesanan :pengiriman="$pesanan->pengiriman" class="mt-4 border-t border-line pt-4" />
                @endif
            </section>
        </div>

        <aside class="panel p-6 lg:sticky lg:top-36">
            <h2 class="text-lg">Rincian harga</h2>
            <x-rincian-harga :pesanan="$pesanan" class="mt-4">
                @if ($pesanan->dibayar_at)
                    <div class="mt-3 flex justify-between border-t border-line pt-3 text-sm">
                        <dt class="text-ink-muted">Dibayar</dt>
                        <dd>@tanggal($pesanan->dibayar_at){{ $pesanan->pembayaran?->metode ? ' · '.$pesanan->pembayaran->labelMetode() : '' }}</dd>
                    </div>
                @endif
            </x-rincian-harga>
            <a href="{{ route('pesanan.invoice', $pesanan) }}" class="btn btn-secondary mt-6 w-full">
                <x-icon name="download" /> Unduh invoice
            </a>
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
