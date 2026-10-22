<div class="mx-auto max-w-6xl px-4 pt-8 sm:px-6">
    <x-breadcrumb :items="['Beranda' => route('home'), 'Produk' => route('produk'), $product->nama_barang => null]" />

    <x-flash class="mt-6" />

    <div class="mt-6 grid gap-10 md:grid-cols-2">
        <div class="card overflow-hidden">
            <img src="{{ url('build/images') }}/{{ $product->gambar }}" alt="{{ $product->nama_barang }}"
                class="aspect-square w-full object-cover">
        </div>

        <div class="flex flex-col">
            @if ($product->stok_barang >= 1)
                <span class="badge badge-ok self-start"><x-icon name="check" class="size-3" /> Tersedia</span>
            @else
                <span class="badge badge-bad self-start"><x-icon name="x" class="size-3" /> Stok Habis</span>
            @endif

            <h1 class="mt-4 text-4xl font-bold">{{ $product->nama_barang }}</h1>
            <p class="mt-3 text-3xl font-semibold text-brand">@rupiah($product->harga_barang)</p>

            <dl class="mt-8 grid grid-cols-2 gap-4">
                <div class="rounded-2xl bg-surface-muted p-4">
                    <dt class="text-xs font-semibold tracking-wide text-ink-muted uppercase">Varian rasa</dt>
                    <dd class="mt-1 font-semibold">{{ $product->nama_barang }}</dd>
                </div>
                <div class="rounded-2xl bg-surface-muted p-4">
                    <dt class="text-xs font-semibold tracking-wide text-ink-muted uppercase">Stok tersedia</dt>
                    <dd class="mt-1 font-semibold">{{ $product->stok_barang }} pcs</dd>
                </div>
            </dl>

            <form wire:submit="masukkankeranjang" class="mt-8 border-t border-line pt-8">
                <label for="jumlah_pesanan" class="label">Jumlah yang ingin dipesan</label>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <input id="jumlah_pesanan" type="number" min="1" max="{{ $product->stok_barang }}"
                        class="input sm:w-32 @error('jumlah_pesanan') input-invalid @enderror"
                        wire:model="jumlah_pesanan" required>
                    <button type="submit" class="btn btn-primary flex-1" @disabled($product->stok_barang < 1)
                        wire:loading.attr="disabled">
                        <x-icon name="shopping-cart" /> Masukkan ke pesanan
                    </button>
                </div>
                @error('jumlah_pesanan')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </form>
        </div>
    </div>
</div>
