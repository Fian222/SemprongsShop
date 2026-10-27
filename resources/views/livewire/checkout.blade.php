<div class="wrap pt-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <x-breadcrumb :items="['Beranda' => route('home'), 'Tas belanja' => route('keranjang'), 'Checkout' => null]" />
        <x-langkah-pesan :aktif="2" />
    </div>

    <h1 class="mt-4 text-4xl md:text-5xl">Checkout</h1>

    @error('keranjang')
        <x-alert type="danger" class="mt-6">
            {{ $message }} <a wire:navigate href="{{ route('keranjang') }}" class="underline underline-offset-2">Ubah tas belanja</a>
        </x-alert>
    @enderror

    <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_400px] lg:items-start">
        <div class="flex flex-col gap-6">
            <section class="card p-6 sm:p-8" aria-labelledby="judul-alamat">
                <div class="flex items-center justify-between gap-3">
                    <h2 id="judul-alamat" class="text-xl">Alamat pengiriman</h2>
                    @if ($alamats->isNotEmpty() && ! $tambahAlamat)
                        <button type="button" wire:click="$set('tambahAlamat', true)" class="btn btn-secondary btn-sm">
                            <x-icon name="plus" /> Alamat baru
                        </button>
                    @endif
                </div>

                @if ($alamats->isNotEmpty())
                    <fieldset class="mt-5">
                        <legend class="sr-only">Pilih alamat</legend>
                        <div class="flex flex-col gap-2">
                            @foreach ($alamats as $alamat)
                                <label wire:key="alamat-{{ $alamat->id }}"
                                    class="flex cursor-pointer gap-3 rounded-xs border border-line p-4 transition-colors hover:border-ink has-checked:border-ink has-checked:bg-surface-muted has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-gold">
                                    <input type="radio" name="alamat" value="{{ $alamat->id }}" wire:model.live="alamatId" class="mt-1 size-4 accent-brand">
                                    <span class="text-[15px]">
                                        <span class="font-medium">{{ $alamat->penerima }}</span>
                                        <span class="text-ink-muted">· {{ $alamat->label }}</span>
                                        @if ($alamat->utama)
                                            <span class="badge badge-low ml-1">Utama</span>
                                        @endif
                                        <span class="mt-1 block text-ink-muted">{{ $alamat->no_hp }}</span>
                                        <span class="block text-ink-muted">{{ $alamat->alamat }}, {{ $alamat->kode_pos }}</span>
                                        @if ($alamat->area_nama)
                                            <span class="block text-sm text-ink-muted">{{ $alamat->area_nama }}</span>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                @endif
                @error('alamatId') <p class="field-error" role="alert">{{ $message }}</p> @enderror

                @if ($tambahAlamat)
                    <form wire:submit="simpanAlamat" class="mt-6 flex flex-col gap-5 border-t border-line pt-6">
                        <h3 class="text-lg">Alamat baru</h3>
                        <x-form-alamat :form="$form" />
                        <div class="flex gap-2">
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <x-icon name="check" /> Simpan alamat
                            </button>
                            @if ($alamats->isNotEmpty())
                                <button type="button" wire:click="$set('tambahAlamat', false)" class="btn btn-secondary">Batal</button>
                            @endif
                        </div>
                    </form>
                @endif
            </section>

            <section class="card p-6 sm:p-8" aria-labelledby="judul-kurir">
                <h2 id="judul-kurir" class="text-xl">Kurir</h2>
                @if ($pesanOngkir)
                    <x-alert type="danger" class="mt-5">
                        {{ $pesanOngkir }}
                        @if ($ongkirPerluUbahAlamat)
                            <a wire:navigate href="{{ route('alamat') }}" class="underline underline-offset-2">Buka buku alamat</a>
                        @else
                            <button type="button" wire:click="muatOngkir" class="underline underline-offset-2">Coba lagi</button>
                        @endif
                    </x-alert>
                @elseif (empty($pilihanKurir))
                    <p class="mt-3 text-[15px] text-ink-muted">Pilih alamat pengiriman untuk melihat pilihan kurir.</p>
                @else
                    <fieldset class="mt-5">
                        <legend class="sr-only">Pilih kurir dan layanan</legend>
                        <div class="grid gap-2 sm:grid-cols-2">
                            @foreach ($pilihanKurir as $pilihan)
                                <label wire:key="kurir-{{ $pilihan['kunci'] }}"
                                    class="flex cursor-pointer items-center gap-3 rounded-xs border border-line p-4 transition-colors hover:border-ink has-checked:border-ink has-checked:bg-surface-muted has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-gold">
                                    <input type="radio" name="kurir" value="{{ $pilihan['kunci'] }}" wire:model.live="kurir" class="size-4 accent-brand">
                                    <span class="flex-1 text-[15px]">
                                        <span class="block font-medium">{{ $pilihan['label'] }}</span>
                                        <span class="text-sm text-ink-muted">Estimasi {{ $pilihan['estimasi'] }}</span>
                                    </span>
                                    <span class="price text-[15px] whitespace-nowrap">@rupiah($pilihan['ongkir'])</span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                @endif
                @error('kurir') <p class="field-error" role="alert">{{ $message }}</p> @enderror
            </section>

            <section class="card" aria-labelledby="judul-barang">
                <h2 id="judul-barang" class="border-b border-line px-6 py-5 text-xl">Isi pesanan</h2>
                <ul class="divide-y divide-line">
                    @foreach ($keranjang?->items ?? [] as $item)
                        <li wire:key="item-{{ $item->id }}" class="flex items-center gap-4 px-6 py-4">
                            <x-foto-produk :foto="$item->varian->produk->fotoUtama" class="size-14 shrink-0 rounded-xs bg-surface-muted" />
                            <div class="min-w-0 flex-1 text-[15px]">
                                <p class="truncate font-medium">{{ $item->varian->produk->nama }}</p>
                                <p class="text-sm text-ink-muted tabular-nums">{{ $item->varian->nama }} · {{ $item->jumlah }} × @rupiah($item->varian->harga)</p>
                            </div>
                            <p class="price whitespace-nowrap">@rupiah($item->jumlahHarga())</p>
                        </li>
                    @endforeach
                </ul>
            </section>
        </div>

        <aside class="panel p-6 lg:sticky lg:top-36">
            <h2 class="text-lg">Ringkasan</h2>

            <div class="mt-4 border-b border-line pb-5">
                @if ($voucherDiterima)
                    <div class="flex items-center justify-between gap-3 rounded-xs border border-dashed border-gold bg-surface px-3 py-2.5">
                        <span class="flex min-w-0 items-center gap-2 text-[15px]">
                            <x-icon name="ticket" class="size-[18px] shrink-0 text-gold" />
                            <span class="truncate font-medium tracking-wide">{{ $voucherDiterima }}</span>
                        </span>
                        <button type="button" wire:click="hapusVoucher" class="text-sm text-ink-muted underline underline-offset-2 hover:text-ink"
                            aria-label="Lepas voucher {{ $voucherDiterima }}">Lepas</button>
                    </div>
                    @if ($pesanVoucher)
                        <p class="field-error" role="alert">{{ $pesanVoucher }}</p>
                    @endif
                @else
                    <form wire:submit="pakaiVoucher">
                        <label for="kode-voucher" class="label">Kode voucher</label>
                        <div class="flex gap-2">
                            <input id="kode-voucher" type="text" wire:model="kodeVoucher" autocomplete="off" autocapitalize="characters" spellcheck="false"
                                maxlength="30" placeholder="mis. HEMAT10" @error('kodeVoucher') aria-invalid="true" aria-describedby="kode-voucher-galat" @enderror
                                class="input min-w-0 flex-1 uppercase placeholder:normal-case @error('kodeVoucher') input-invalid @enderror">
                            <button type="submit" class="btn btn-secondary" wire:loading.attr="disabled" wire:target="pakaiVoucher">Pakai</button>
                        </div>
                    </form>
                @endif
                @error('kodeVoucher') <p id="kode-voucher-galat" class="field-error" role="alert">{{ $message }}</p> @enderror
            </div>

            <dl class="mt-4 flex flex-col gap-2 text-[15px]">
                <div class="flex justify-between">
                    <dt class="text-ink-muted">Subtotal</dt>
                    <dd class="tabular-nums">@rupiah($subtotal)</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-ink-muted">Ongkir</dt>
                    <dd class="tabular-nums">{{ $ongkir === null ? 'Pilih kurir' : \App\Support\Format::rupiah($ongkir) }}</dd>
                </div>
                @if ($diskon)
                    <div class="flex justify-between">
                        <dt class="text-ink-muted">Diskon {{ $voucherDiterima }}</dt>
                        <dd class="tabular-nums text-ok">−@rupiah($diskon)</dd>
                    </div>
                @endif
                <div class="mt-2 flex items-baseline justify-between border-t border-line pt-3">
                    <dt class="font-medium">Total</dt>
                    <dd class="price text-[28px] leading-none">@rupiah($subtotal + ($ongkir ?? 0) - $diskon)</dd>
                </div>
            </dl>
            <button type="button" wire:click="bayar" class="btn btn-primary mt-5 w-full" wire:loading.attr="disabled" wire:target="bayar">
                <span wire:loading.remove wire:target="bayar">Bayar sekarang</span>
                <span wire:loading wire:target="bayar">Menyiapkan pembayaran…</span>
            </button>
            <p class="mt-3 text-center text-[13px] leading-relaxed text-ink-muted">
                Bayar lewat QRIS, e-wallet, virtual account, atau kartu. Stok dipesankan untukmu selama {{ config('pembayaran.batas_bayar_jam') }} jam.
            </p>
        </aside>
    </div>
</div>
