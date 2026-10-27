<div class="wrap pt-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <x-breadcrumb :items="['Beranda' => route('home'), 'Tas belanja' => null]" />
        @if ($items->isNotEmpty())
            <x-langkah-pesan :aktif="1" />
        @endif
    </div>

    <h1 class="mt-4 text-4xl md:text-5xl">Tas belanja</h1>

    @if ($items->isEmpty())
        <div class="card mt-6">
            <x-empty icon="shopping-bag">
                Tas belanjamu masih kosong. Pilih rasa dulu, lalu tentukan kemasan dan jumlahnya.
                <x-slot:action>
                    <a wire:navigate href="{{ route('produk') }}" class="btn btn-primary">Pilih rasa</a>
                </x-slot:action>
            </x-empty>
        </div>
    @else
        @php($adaKendala = $items->contains(fn ($item) => $item->kendala()))

        <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_360px] lg:items-start">
            <ul class="card divide-y divide-line">
                @foreach ($items as $item)
                    @php($kendala = $item->kendala())
                    <li wire:key="item-{{ $item->id }}" class="flex gap-4 p-4">
                        <x-foto-produk :foto="$item->varian->produk->fotoUtama" class="size-20 shrink-0 rounded-xs bg-surface-muted sm:size-24" />
                        <div class="flex min-w-0 flex-1 flex-col gap-2">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h2 class="truncate text-[15px] font-medium tracking-normal">
                                        <a wire:navigate href="{{ route('produk.detail', $item->varian->produk->slug) }}" class="hover:underline">{{ $item->varian->produk->nama }}</a>
                                    </h2>
                                    <p class="text-sm text-ink-muted">
                                        {{ $item->varian->nama }} · @rupiah($item->varian->harga)
                                        @if ($item->varian->punyaHargaCoret())
                                            <s class="tabular-nums"><span class="sr-only">Harga sebelumnya </span>@rupiah($item->varian->harga_coret)</s>
                                        @endif
                                    </p>
                                    @if ($kendala)
                                        <span class="badge badge-bad mt-1.5">{{ $kendala }}</span>
                                    @endif
                                </div>
                                <button type="button" wire:click="hapus({{ $item->id }})" wire:loading.attr="disabled"
                                    class="btn btn-danger btn-icon -mt-2 -mr-2" aria-label="Hapus {{ $item->varian->produk->nama }} {{ $item->varian->nama }} dari tas belanja">
                                    <x-icon name="trash" class="size-[18px]" />
                                </button>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex h-10 items-center rounded-xs border border-line bg-surface">
                                    <button type="button" wire:click="ubahJumlah({{ $item->id }}, {{ $item->jumlah - 1 }})" wire:loading.attr="disabled"
                                        aria-label="Kurangi jumlah {{ $item->varian->produk->nama }}"
                                        class="grid h-full w-10 place-items-center rounded-l-xs text-ink-muted hover:bg-surface-muted hover:text-ink">
                                        <x-icon name="minus" />
                                    </button>
                                    <input type="number" min="0" inputmode="numeric" value="{{ $item->jumlah }}"
                                        aria-label="Jumlah {{ $item->varian->produk->nama }} {{ $item->varian->nama }}"
                                        wire:change="ubahJumlah({{ $item->id }}, $event.target.value)"
                                        class="h-full w-12 min-w-0 [appearance:textfield] bg-transparent text-center text-[15px] font-medium tabular-nums [&::-webkit-inner-spin-button]:appearance-none">
                                    <button type="button" wire:click="ubahJumlah({{ $item->id }}, {{ $item->jumlah + 1 }})" wire:loading.attr="disabled"
                                        aria-label="Tambah jumlah {{ $item->varian->produk->nama }}"
                                        class="grid h-full w-10 place-items-center rounded-r-xs text-ink-muted hover:bg-surface-muted hover:text-ink">
                                        <x-icon name="plus" />
                                    </button>
                                </div>
                                <p class="price text-base whitespace-nowrap">@rupiah($item->jumlahHarga())</p>
                            </div>
                            @error("jumlah.{$item->id}")
                                <p class="field-error mt-0" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                    </li>
                @endforeach
            </ul>

            <aside class="panel p-6 lg:sticky lg:top-36">
                <h2 class="text-lg">Ringkasan</h2>
                <dl class="mt-4 flex flex-col gap-2 text-[15px]">
                    <div class="flex justify-between">
                        <dt class="text-ink-muted">{{ $keranjang->jumlahBarang() }} barang</dt>
                        <dd class="tabular-nums">@rupiah($keranjang->subtotal())</dd>
                    </div>
                    <div class="mt-2 flex items-baseline justify-between border-t border-line pt-3">
                        <dt class="font-medium">Subtotal</dt>
                        <dd class="price text-2xl">@rupiah($keranjang->subtotal())</dd>
                    </div>
                </dl>
                @if ($adaKendala)
                    <p class="mt-4 text-sm text-bad">Ubah atau hapus barang yang bertanda merah sebelum checkout.</p>
                    <button type="button" disabled class="btn btn-primary mt-4 w-full">Lanjut ke checkout</button>
                @else
                    <a wire:navigate href="{{ route('checkout') }}" class="btn btn-primary mt-5 w-full">Lanjut ke checkout</a>
                @endif
                <a wire:navigate href="{{ route('produk') }}" class="btn btn-secondary mt-2 w-full">Tambah rasa lain</a>
                @guest
                    <p class="mt-3 text-center text-[13px] text-ink-muted">Kamu akan diminta masuk saat checkout. Isi tas tetap tersimpan.</p>
                @endguest
            </aside>
        </div>
    @endif
</div>
