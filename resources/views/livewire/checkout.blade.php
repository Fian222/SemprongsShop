<div class="mx-auto max-w-6xl px-4 pt-8 sm:px-6">
    <x-breadcrumb :items="['Beranda' => route('home'), 'Pesanan' => route('pesanann'), 'Checkout' => null]" />

    <div class="mt-6 flex items-center gap-3">
        <a wire:navigate href="{{ route('pesanann') }}" class="btn btn-secondary size-10 p-0" aria-label="Kembali ke pesanan">
            <x-icon name="arrow-left" />
        </a>
        <h1 class="text-4xl font-bold">Checkout</h1>
    </div>

    <x-flash type="danger" class="mt-6" />

    <div class="mt-8 grid gap-8 lg:grid-cols-2">
        <section class="card p-6 sm:p-8">
            <h2 class="text-2xl font-bold">Informasi pembayaran</h2>
            <p class="mt-2 text-ink-muted">Silakan transfer ke rekening di bawah ini sebesar:</p>
            <p class="mt-4 text-4xl font-bold text-brand">@rupiah($total_harga)</p>

            <div class="mt-6 flex items-center gap-4 rounded-2xl bg-surface-muted p-5">
                <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-brand-soft text-brand">
                    <x-icon name="landmark" class="size-6" />
                </span>
                <div>
                    <p class="font-semibold">Bank BCA</p>
                    <p class="text-sm text-ink-muted">
                        No. rekening <span class="font-mono font-semibold text-ink">12722993</span>
                        a.n. <span class="font-semibold text-ink">Akhmat Oktafian</span>
                    </p>
                </div>
            </div>
        </section>

        <section class="card p-6 sm:p-8">
            <h2 class="text-2xl font-bold">Informasi pengiriman</h2>
            <form wire:submit="checkout" class="mt-6 flex flex-col gap-5">
                <div>
                    <label for="nohp" class="label">No. HP</label>
                    <input type="tel" id="nohp" wire:model="nohp" autocomplete="tel" autofocus
                        placeholder="0812-3456-7890" aria-describedby="nohp-petunjuk"
                        class="input @error('nohp') input-invalid @enderror">
                    @error('nohp')
                        <p class="field-error">{{ $message }}</p>
                    @else
                        <p id="nohp-petunjuk" class="mt-1.5 text-xs text-ink-muted">Nomor HP aktif, 10–13 digit, diawali 08 atau +62.</p>
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

                <button type="submit" class="btn btn-primary w-full" wire:loading.attr="disabled">
                    Konfirmasi pesanan <x-icon name="arrow-right" />
                </button>
            </form>
        </section>
    </div>
</div>
