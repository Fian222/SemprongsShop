<div class="wrap pt-4">
    <x-breadcrumb :items="[
        'Beranda' => route('home'),
        'Semua semprong' => route('produk'),
        $produk->kategori->nama => route('produk', ['kategori' => $produk->kategori->slug]),
        $produk->nama => null,
    ]" />

    <div class="mt-5 grid gap-8 md:grid-cols-2 lg:gap-12">
        {{-- galeri: foto besar dan deretan thumbnail; ganti foto di sisi klien saja --}}
        <div x-data="{ aktif: 0 }" class="flex flex-col gap-3">
            <div class="relative aspect-square overflow-hidden rounded-xs bg-surface-muted">
                @forelse ($produk->fotos as $foto)
                    <x-foto-produk :foto="$foto" :alt="$produk->nama.', foto '.$loop->iteration" x-show="aktif === {{ $loop->index }}"
                        @class(['absolute inset-0 size-full', 'opacity-70 grayscale-[.85]' => $varian->habis()]) />
                @empty
                    <x-foto-produk :foto="null" :alt="$produk->nama" class="size-full" />
                @endforelse
            </div>
            @if ($produk->fotos->count() > 1)
                <div class="grid grid-cols-5 gap-2">
                    @foreach ($produk->fotos as $foto)
                        <button type="button" wire:key="thumb-{{ $foto->id }}" @click="aktif = {{ $loop->index }}"
                            :aria-current="aktif === {{ $loop->index }}" aria-label="Lihat foto {{ $loop->iteration }}"
                            class="aspect-square overflow-hidden rounded-xs border-2 border-transparent aria-[current=true]:border-gold">
                            <x-foto-produk :foto="$foto" class="size-full" />
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="flex flex-col">
            <p class="text-sm tracking-wide text-gold uppercase">{{ $produk->kategori->nama }}</p>
            <h1 class="mt-1 text-4xl leading-tight md:text-5xl">{{ $produk->nama }}</h1>

            <p class="mt-3 flex items-baseline gap-3" aria-live="polite">
                <span class="price text-3xl">@rupiah($varian->harga)</span>
                @if ($varian->punyaHargaCoret())
                    <s class="text-lg text-ink-muted tabular-nums"><span class="sr-only">Harga sebelumnya </span>@rupiah($varian->harga_coret)</s>
                @endif
            </p>

            <p class="mt-3 flex items-center gap-2 text-[15px]">
                @if ($varian->habis())
                    <span class="badge badge-out">Habis</span>
                    <span class="text-ink-muted">Sedang tidak tersedia</span>
                @elseif ($varian->menipis())
                    <span class="badge badge-low">Tinggal {{ $varian->stok }}</span>
                    <span class="text-ink-muted">Stok menipis</span>
                @else
                    <span class="badge badge-ok"><x-icon name="check" class="size-3" /> Tersedia</span>
                    <span class="text-ink-muted">Stok {{ $varian->stok }}</span>
                @endif
            </p>

            <x-flash class="mt-6" />

            <form wire:submit="tambahKeKeranjang" class="mt-6 border-t border-line pt-6">
                <fieldset>
                    <legend class="label">Kemasan</legend>
                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach ($produk->varianAktif as $pilihan)
                            <label wire:key="varian-{{ $pilihan->id }}"
                                class="flex cursor-pointer items-center justify-between gap-3 rounded-xs border border-line px-4 py-3 text-[15px] transition-colors
                                    hover:border-ink has-checked:border-ink has-checked:bg-surface-muted has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-gold
                                    has-disabled:cursor-not-allowed has-disabled:border-dashed has-disabled:text-ink-muted has-disabled:hover:border-line">
                                <input type="radio" name="varian" wire:model.live="varianId" class="sr-only"
                                    value="{{ $pilihan->id }}" @disabled($pilihan->habis())>
                                <span>
                                    <span class="block font-medium">{{ $pilihan->nama }}</span>
                                    <span class="text-sm text-ink-muted">{{ $pilihan->berat }} g</span>
                                </span>
                                <span class="text-right text-sm tabular-nums">
                                    {{ $pilihan->habis() ? 'Habis' : \App\Support\Format::rupiah($pilihan->harga) }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <label for="jumlah" class="label mt-5">Jumlah</label>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <div class="flex h-11 items-center rounded-xs border border-line bg-surface sm:w-36">
                        <button type="button" aria-label="Kurangi jumlah" @disabled($varian->habis())
                            @click="$refs.qty.stepDown(); $refs.qty.dispatchEvent(new Event('input'))"
                            class="grid h-full w-11 place-items-center rounded-l-xs text-ink-muted hover:bg-surface-muted hover:text-ink disabled:opacity-40">
                            <x-icon name="minus" />
                        </button>
                        <input id="jumlah" x-ref="qty" type="number" min="1" max="{{ $varian->stok }}" inputmode="numeric"
                            class="h-full w-full min-w-0 [appearance:textfield] bg-transparent text-center text-[15px] font-medium tabular-nums [&::-webkit-inner-spin-button]:appearance-none"
                            wire:model="jumlah" required @disabled($varian->habis())>
                        <button type="button" aria-label="Tambah jumlah" @disabled($varian->habis())
                            @click="$refs.qty.stepUp(); $refs.qty.dispatchEvent(new Event('input'))"
                            class="grid h-full w-11 place-items-center rounded-r-xs text-ink-muted hover:bg-surface-muted hover:text-ink disabled:opacity-40">
                            <x-icon name="plus" />
                        </button>
                    </div>
                    <button type="submit" @disabled($varian->habis())
                        @class(['btn w-full sm:w-auto sm:flex-1', 'btn-primary' => ! $varian->habis(), 'bg-surface-muted text-ink-muted disabled:opacity-100' => $varian->habis()])
                        wire:loading.attr="disabled">
                        <x-icon name="shopping-bag" /> {{ $varian->habis() ? 'Stok habis' : 'Masukkan ke tas' }}
                    </button>
                </div>
                @error('jumlah')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </form>

            <div class="mt-8 flex flex-col gap-6 border-t border-line pt-6">
                <div>
                    <h2 class="text-xl">Tentang produk ini</h2>
                    <p class="mt-2 leading-relaxed whitespace-pre-line text-ink-muted">{{ $produk->deskripsi }}</p>
                </div>
                @if ($produk->komposisi)
                    <div>
                        <h2 class="text-xl">Komposisi</h2>
                        <p class="mt-2 leading-relaxed whitespace-pre-line text-ink-muted">{{ $produk->komposisi }}</p>
                    </div>
                @endif
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-ink-muted">Berat bersih</dt>
                        <dd class="mt-0.5 font-medium tabular-nums">{{ $varian->berat }} g</dd>
                    </div>
                    <div>
                        <dt class="text-ink-muted">SKU</dt>
                        <dd class="mt-0.5 font-medium">{{ $varian->sku }}</dd>
                    </div>
                </dl>
            </div>

            <ul class="mt-8 flex flex-col gap-3 rounded-xs bg-surface-muted p-5 text-sm">
                <li class="flex gap-3"><x-icon name="package" class="mt-0.5 size-[18px] text-brand" />
                    <span><b class="font-medium">Dibuat setelah dipesan</b>, lalu dikemas rapat supaya tetap garing.</span></li>
                <li class="flex gap-3"><x-icon name="landmark" class="mt-0.5 size-[18px] text-brand" />
                    <span><b class="font-medium">Bayar lewat QRIS, e-wallet, VA, atau kartu</b> setelah checkout.</span></li>
                <li class="flex gap-3"><x-icon name="circle-check" class="mt-0.5 size-[18px] text-brand" />
                    <span><b class="font-medium">Status otomatis</b> begitu pembayaran diterima, pantau di Pesanan saya.</span></li>
            </ul>
        </div>
    </div>
</div>
