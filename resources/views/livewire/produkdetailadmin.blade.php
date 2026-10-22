<div>
    <a wire:navigate href="{{ route('admin.produk') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-ink-muted hover:text-brand">
        <x-icon name="arrow-left" /> Kembali ke produk
    </a>

    <x-flash class="mt-6" />

    <div class="mt-6 grid gap-8 md:grid-cols-2">
        <div class="card overflow-hidden">
            <img src="{{ url('build/images') }}/{{ $product->gambar }}" alt="{{ $product->nama_barang }}"
                class="aspect-square w-full object-cover">
        </div>

        <div>
            <h1 class="text-3xl font-bold">{{ $product->nama_barang }}</h1>
            <p class="mt-2 text-2xl font-semibold text-brand">@rupiah($product->harga_barang)</p>

            <dl class="mt-6 grid grid-cols-2 gap-4">
                <div class="rounded-2xl bg-surface-muted p-4">
                    <dt class="text-xs font-semibold tracking-wide text-ink-muted uppercase">Varian rasa</dt>
                    <dd class="mt-1 font-semibold">{{ $product->nama_barang }}</dd>
                </div>
                <div class="rounded-2xl bg-surface-muted p-4">
                    <dt class="text-xs font-semibold tracking-wide text-ink-muted uppercase">Stok tersedia</dt>
                    <dd class="mt-1 font-semibold">{{ $product->stok_barang }} pcs</dd>
                </div>
            </dl>

            <form wire:submit="edit({{ $product->id }})" class="card mt-6 flex flex-col gap-5 p-6">
                <h2 class="font-sans text-base font-semibold">Ubah produk</h2>
                <div>
                    <label for="jumlah_tambah" class="label">Tambah / kurangi stok</label>
                    <input id="jumlah_tambah" type="number" class="input" wire:model="jumlah_tambah" placeholder="mis. 10 atau -3">
                </div>
                <div>
                    <label for="ubah_harga" class="label">Harga baru (Rp)</label>
                    <input id="ubah_harga" type="number" min="0" class="input" wire:model="ubah_harga" placeholder="{{ $product->harga_barang }}">
                </div>
                <button type="submit" class="btn btn-primary w-full" wire:loading.attr="disabled">
                    <x-icon name="check" /> Simpan perubahan
                </button>
            </form>
        </div>
    </div>
</div>
