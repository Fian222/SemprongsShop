<div class="wrap pt-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <x-breadcrumb :items="['Beranda' => route('home'), 'Tas belanja' => route('pesanann'), 'Checkout' => null]" />
        <x-langkah-pesan :aktif="2" />
    </div>

    <h1 class="mt-4 text-4xl md:text-5xl">Checkout</h1>

    <x-flash type="danger" class="mt-6" />

    <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_400px] lg:items-start">
        <section class="card p-6 sm:p-8">
            <h2 class="text-xl">Alamat pengiriman</h2>
            <form wire:submit="checkout" id="form-checkout" class="mt-6 flex flex-col gap-5">
                <div>
                    <label for="nohp" class="label">No. HP</label>
                    <input type="tel" id="nohp" wire:model="nohp" autocomplete="tel" autofocus
                        placeholder="0812-3456-7890" aria-describedby="nohp-petunjuk"
                        class="input @error('nohp') input-invalid @enderror">
                    @error('nohp')
                        <p class="field-error">{{ $message }}</p>
                    @else
                        <p id="nohp-petunjuk" class="hint">Nomor HP aktif, 10–13 digit, diawali 08 atau +62.</p>
                    @enderror
                </div>

                <div>
                    <label for="alamat" class="label">Alamat lengkap</label>
                    <textarea id="alamat" wire:model="alamat" rows="4" autocomplete="street-address"
                        placeholder="Nama jalan, nomor rumah, kecamatan, kota"
                        class="input @error('alamat') input-invalid @enderror"></textarea>
                    @error('alamat')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
            </form>
        </section>

        <aside class="panel p-6 lg:sticky lg:top-36">
            <h2 class="text-lg">Pembayaran</h2>
            <p class="mt-1 text-sm text-ink-muted">Transfer tepat sebesar total ini setelah konfirmasi pesanan.</p>
            <p class="price mt-4 text-[34px] leading-none">@rupiah($total_harga)</p>

            <div x-data="{ disalin: false, nomor: @js(config('toko.rekening.nomor')) }" class="mt-5 rounded-xs bg-surface p-4">
                <div class="flex items-center gap-3">
                    <span class="grid size-10 shrink-0 place-items-center rounded-xs bg-brand-soft text-brand">
                        <x-icon name="landmark" class="size-5" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm text-ink-muted">Bank {{ config('toko.rekening.bank') }} a.n. {{ config('toko.rekening.nama') }}</p>
                        <p class="text-lg font-medium tracking-wide tabular-nums">{{ config('toko.rekening.nomor') }}</p>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm"
                        @click="navigator.clipboard.writeText(nomor).then(() => { disalin = true; setTimeout(() => disalin = false, 2000) })">
                        <x-icon name="copy" x-show="!disalin" />
                        <x-icon name="check" x-cloak x-show="disalin" />
                        <span x-text="disalin ? 'Disalin' : 'Salin'">Salin</span>
                    </button>
                </div>
            </div>

            <button type="submit" form="form-checkout" class="btn btn-primary mt-5 w-full" wire:loading.attr="disabled">
                Konfirmasi pesanan
            </button>
            <p class="mt-3 text-center text-[13px] text-ink-muted">Status pesanan bisa dipantau di Riwayat.</p>
        </aside>
    </div>
</div>
