<div>
    <a wire:navigate href="{{ route('admin.produk') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-ink-muted hover:text-ink">
        <x-icon name="arrow-left" /> Kembali ke produk
    </a>

    <x-flash class="mt-6" />

    <div class="mt-6 grid gap-6 md:grid-cols-[minmax(0,2fr)_minmax(0,3fr)]">
        <div class="card overflow-hidden">
            <img src="{{ url('build/images') }}/{{ $product->gambar }}" alt="{{ $product->nama_barang }}"
                class="aspect-square w-full bg-surface-muted object-cover">
            <dl class="grid grid-cols-2 divide-x divide-line border-t border-line text-sm">
                <div class="p-4">
                    <dt class="text-ink-muted">Harga sekarang</dt>
                    <dd class="price mt-0.5 text-lg">@rupiah($product->harga_barang)</dd>
                </div>
                <div class="p-4">
                    <dt class="text-ink-muted">Stok sekarang</dt>
                    <dd class="price mt-0.5 text-lg">{{ $product->stok_barang }} pcs</dd>
                </div>
            </dl>
        </div>

        <div>
            <h1 class="text-4xl leading-tight">{{ $product->nama_barang }}</h1>

            <form wire:submit="edit({{ $product->id }})" class="card mt-6 flex flex-col gap-5 p-6">
                <h2 class="text-base tracking-normal">Ubah produk</h2>
                <div>
                    <label for="jumlah_tambah" class="label">Tambah / kurangi stok</label>
                    <input id="jumlah_tambah" type="number" class="input" wire:model="jumlah_tambah" placeholder="mis. 10 atau -3"
                        aria-describedby="jumlah_tambah-petunjuk">
                    <p id="jumlah_tambah-petunjuk" class="hint">Angka negatif mengurangi stok.</p>
                </div>
                <div>
                    <label for="ubah_harga" class="label">Harga baru (Rp)</label>
                    <input id="ubah_harga" type="number" min="0" class="input" wire:model="ubah_harga" placeholder="{{ $product->harga_barang }}">
                </div>
                <button type="submit" class="btn btn-primary self-start" wire:loading.attr="disabled">
                    <x-icon name="check" /> Simpan perubahan
                </button>
            </form>
        </div>
    </div>
</div>
