@php($status = $pesanan->status)

<div>
    <a wire:navigate href="{{ route('admin.pesanan') }}" class="inline-flex items-center gap-1 text-sm text-ink-muted hover:text-ink">
        <x-icon name="arrow-left" class="size-4" /> Semua pesanan
    </a>

    <div class="mt-3 flex flex-wrap items-center gap-3">
        <h1 class="text-4xl leading-tight tabular-nums">{{ $pesanan->nomor }}</h1>
        <x-status-pesanan :status="$status" class="text-sm" />
    </div>
    <p class="mt-1 text-ink-muted">Dibuat @tanggal($pesanan->created_at)</p>

    <x-flash class="mt-6" />
    @error('status')
        <x-alert type="error" class="mt-6">{{ $message }}</x-alert>
    @enderror

    <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_380px] lg:items-start">
        <div class="flex flex-col gap-6">
            @if ($status === \App\Enums\StatusPesanan::Dibayar)
                <section class="card flex flex-col gap-4 p-6 sm:flex-row sm:items-center">
                    <div class="flex-1">
                        <h2 class="font-sans text-base font-medium tracking-normal">Pembayaran sudah diterima</h2>
                        <p class="mt-0.5 text-sm text-ink-muted">Tandai Diproses begitu paketnya mulai disiapkan.</p>
                    </div>
                    <button type="button" wire:click="proses" wire:loading.attr="disabled" class="btn btn-primary">
                        <x-icon name="package" /> Tandai diproses
                    </button>
                </section>
            @elseif ($status === \App\Enums\StatusPesanan::Diproses)
                <section class="card p-6">
                    <h2 class="font-sans text-base font-medium tracking-normal">Kirim paket</h2>
                    <p class="mt-0.5 text-sm text-ink-muted">Serahkan paket ke {{ $pesanan->pengiriman?->label() ?? 'kurir' }}, lalu input nomor resinya.</p>
                    <form wire:submit="kirim" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-start">
                        <div class="flex-1">
                            <label for="resi" class="sr-only">Nomor resi</label>
                            <input id="resi" wire:model="resi" type="text" autocomplete="off" placeholder="Nomor resi"
                                class="input uppercase tabular-nums placeholder:normal-case" @error('resi') aria-invalid="true" aria-describedby="resi-error" @enderror>
                            @error('resi') <p id="resi-error" class="field-error" role="alert">{{ $message }}</p> @enderror
                        </div>
                        <button type="submit" wire:loading.attr="disabled" class="btn btn-primary">
                            <x-icon name="truck" /> Tandai dikirim
                        </button>
                    </form>
                </section>
            @elseif ($status === \App\Enums\StatusPesanan::Dikirim)
                <section class="card flex flex-col gap-4 p-6 sm:flex-row sm:items-center">
                    <div class="flex-1">
                        <h2 class="font-sans text-base font-medium tracking-normal">Paket dalam pengiriman</h2>
                        <p class="mt-0.5 text-sm text-ink-muted">Tandai Selesai bila paketnya sudah diterima pembeli.</p>
                    </div>
                    <button type="button" wire:click="selesaikan" wire:loading.attr="disabled" class="btn btn-primary">
                        <x-icon name="check" /> Tandai selesai
                    </button>
                </section>
            @elseif ($status === \App\Enums\StatusPesanan::MenungguPembayaran)
                <section class="card flex flex-col gap-4 p-6 sm:flex-row sm:items-center">
                    <div class="flex-1">
                        <h2 class="font-sans text-base font-medium tracking-normal">Menunggu pembayaran sampai @tanggal($pesanan->batas_bayar)</h2>
                        <p class="mt-0.5 text-sm text-ink-muted">Membatalkan pesanan mengembalikan stoknya. Pesanan yang belum dibayar tidak bisa dikirim.</p>
                    </div>
                    <button type="button" wire:click="batalkan" wire:loading.attr="disabled"
                        wire:confirm="Batalkan pesanan {{ $pesanan->nomor }}? Stoknya dikembalikan dan pesanan tidak bisa dibayar lagi."
                        class="btn btn-danger">
                        <x-icon name="x" /> Batalkan pesanan
                    </button>
                </section>
            @endif

            @if ($pesanan->pengiriman?->resi)
                <x-pelacakan :pengiriman="$pesanan->pengiriman" :pelacakan="$pelacakan" :gagal="$pelacakanGagal" />
            @endif

            <x-isi-pesanan :pesanan="$pesanan" sku />

            <section class="card grid gap-6 p-6 sm:grid-cols-2">
                <div>
                    <h2 class="flex items-center gap-2 text-xl"><x-icon name="user" class="size-5 text-brand" /> Pembeli</h2>
                    <div class="mt-3 text-[15px]">
                        <p class="font-medium">{{ $pesanan->user->name }}</p>
                        <p class="text-ink-muted">{{ $pesanan->user->email }}</p>
                    </div>
                </div>
                <div>
                    <h2 class="flex items-center gap-2 text-xl"><x-icon name="map-pin" class="size-5 text-brand" /> Dikirim ke</h2>
                    <x-alamat-pesanan :pesanan="$pesanan" class="mt-3" />
                </div>
                @if ($pesanan->pengiriman)
                    <x-kurir-pesanan :pengiriman="$pesanan->pengiriman" class="border-t border-line pt-4 sm:col-span-2" />
                @endif
            </section>
        </div>

        <aside class="panel p-6 lg:sticky lg:top-24">
            <h2 class="text-lg">Rincian harga</h2>
            <x-rincian-harga :pesanan="$pesanan" class="mt-4" />

            <h2 class="mt-8 text-lg">Pembayaran</h2>
            @if ($pembayaran = $pesanan->pembayaran)
                <dl class="mt-4 flex flex-col gap-2 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-ink-muted">Status</dt>
                        <dd><span class="badge {{ $pembayaran->status->badge() }}">{{ $pembayaran->status->label() }}</span></dd>
                    </div>
                    @if ($pembayaran->metode)
                        <div class="flex justify-between gap-4">
                            <dt class="text-ink-muted">Metode</dt>
                            <dd>{{ str($pembayaran->metode)->replace('_', ' ')->title() }}</dd>
                        </div>
                    @endif
                    @if ($pembayaran->dibayar_at)
                        <div class="flex justify-between gap-4">
                            <dt class="text-ink-muted">Dibayar</dt>
                            <dd>@tanggal($pembayaran->dibayar_at)</dd>
                        </div>
                    @endif
                    <div class="flex justify-between gap-4">
                        <dt class="text-ink-muted">Referensi</dt>
                        <dd class="tabular-nums">{{ $pembayaran->referensi }}</dd>
                    </div>
                    @if ($pembayaran->transaksi_id)
                        <div class="flex justify-between gap-4">
                            <dt class="text-ink-muted">ID transaksi</dt>
                            <dd class="truncate tabular-nums">{{ $pembayaran->transaksi_id }}</dd>
                        </div>
                    @endif
                </dl>
                @if ($tindakan = $pembayaran->status->tindakan())
                    <p class="mt-4 text-sm text-bad">{{ $tindakan }}</p>
                @endif
            @else
                <p class="mt-3 text-sm text-ink-muted">Pembeli belum membuka jendela pembayaran.</p>
            @endif
        </aside>
    </div>
</div>
