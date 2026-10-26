<div class="wrap pt-4">
    <x-breadcrumb :items="['Beranda' => route('home'), 'Semua semprong' => route('produk'), $product->nama_barang => null]" />

    <div class="mt-5 grid gap-8 md:grid-cols-2 lg:gap-12">
        <div class="overflow-hidden rounded-xs bg-surface-muted">
            <img src="{{ url('build/images') }}/{{ $product->gambar }}" alt="{{ $product->nama_barang }}"
                @class(['aspect-square w-full object-cover', 'opacity-70 grayscale-[.85]' => $product->habis()])>
        </div>

        <div class="flex flex-col">
            <h1 class="text-4xl leading-tight md:text-5xl">{{ $product->nama_barang }}</h1>
            <p class="price mt-3 text-3xl">@rupiah($product->harga_barang)</p>

            <p class="mt-3 flex items-center gap-2 text-[15px]">
                @if ($product->habis())
                    <span class="badge badge-out">Habis</span>
                    <span class="text-ink-muted">Sedang tidak tersedia</span>
                @elseif ($product->menipis())
                    <span class="badge badge-low">Tinggal {{ $product->stok_barang }}</span>
                    <span class="text-ink-muted">Stok menipis</span>
                @else
                    <span class="badge badge-ok"><x-icon name="check" class="size-3" /> Tersedia</span>
                    <span class="text-ink-muted">Stok {{ $product->stok_barang }} pcs</span>
                @endif
            </p>

            <x-flash class="mt-6" />

            <form wire:submit="masukkankeranjang" class="mt-6 border-t border-line pt-6">
                <label for="jumlah_pesanan" class="label">Jumlah</label>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <div class="flex h-11 items-center rounded-xs border border-line bg-surface sm:w-36">
                        <button type="button" aria-label="Kurangi jumlah" @disabled($product->habis())
                            @click="$refs.qty.stepDown(); $refs.qty.dispatchEvent(new Event('input'))"
                            class="grid h-full w-11 place-items-center rounded-l-xs text-ink-muted hover:bg-surface-muted hover:text-ink disabled:opacity-40">
                            <x-icon name="minus" />
                        </button>
                        <input id="jumlah_pesanan" x-ref="qty" type="number" min="1" max="{{ $product->stok_barang }}" inputmode="numeric"
                            class="h-full w-full min-w-0 [appearance:textfield] bg-transparent text-center text-[15px] font-medium tabular-nums [&::-webkit-inner-spin-button]:appearance-none"
                            wire:model="jumlah_pesanan" required @disabled($product->habis())>
                        <button type="button" aria-label="Tambah jumlah" @disabled($product->habis())
                            @click="$refs.qty.stepUp(); $refs.qty.dispatchEvent(new Event('input'))"
                            class="grid h-full w-11 place-items-center rounded-r-xs text-ink-muted hover:bg-surface-muted hover:text-ink disabled:opacity-40">
                            <x-icon name="plus" />
                        </button>
                    </div>
                    <button type="submit" @disabled($product->habis())
                        @class(['btn w-full sm:w-auto sm:flex-1', 'btn-primary' => ! $product->habis(), 'bg-surface-muted text-ink-muted disabled:opacity-100' => $product->habis()])
                        wire:loading.attr="disabled">
                        <x-icon name="shopping-bag" /> {{ $product->habis() ? 'Stok habis' : 'Masukkan ke tas' }}
                    </button>
                </div>
                @error('jumlah_pesanan')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </form>

            <ul class="mt-8 flex flex-col gap-3 rounded-xs bg-surface-muted p-5 text-sm">
                <li class="flex gap-3"><x-icon name="package" class="mt-0.5 size-[18px] text-brand" />
                    <span><b class="font-medium">Dibuat setelah dipesan</b>, lalu dikemas rapat supaya tetap garing.</span></li>
                <li class="flex gap-3"><x-icon name="landmark" class="mt-0.5 size-[18px] text-brand" />
                    <span><b class="font-medium">Bayar lewat transfer {{ config('toko.rekening.bank') }}</b> setelah checkout.</span></li>
                <li class="flex gap-3"><x-icon name="circle-check" class="mt-0.5 size-[18px] text-brand" />
                    <span><b class="font-medium">Dikonfirmasi admin</b>, statusnya bisa dipantau di Riwayat.</span></li>
            </ul>
        </div>
    </div>
</div>
