<div class="wrap pt-4">
    <x-breadcrumb :items="['Beranda' => route('home'), 'Semua semprong' => null]" />

    <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-4xl md:text-5xl">Semua semprong</h1>
            <p class="mt-1 text-ink-muted">
                @if ($cari)
                    {{ $produks->total() }} hasil untuk “{{ $cari }}”
                @else
                    {{ $produks->total() }} produk, dibuat setelah dipesan.
                @endif
            </p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row">
            <label class="relative block sm:w-72">
                <span class="sr-only">Cari produk</span>
                <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3.5 size-5 -translate-y-1/2 text-ink-muted" />
                <input wire:model.live.debounce.300ms="cari" type="search" placeholder="Cari rasa" class="input h-11 pl-11">
            </label>
            <label class="block sm:w-48">
                <span class="sr-only">Urutkan</span>
                <select wire:model.live="urut" class="input h-11">
                    @foreach ($urutan as $nilai => $label)
                        <option value="{{ $nilai }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>
        </div>
    </div>

    @if ($kategoris->isNotEmpty())
        <div class="mt-6 flex gap-2 overflow-x-auto pb-1" role="group" aria-label="Kategori">
            <button type="button" wire:click="$set('kategori', '')" aria-pressed="{{ $kategori === '' ? 'true' : 'false' }}"
                @class(['chip', 'chip-on' => $kategori === ''])>Semua</button>
            @foreach ($kategoris as $item)
                <button type="button" wire:key="kategori-{{ $item->id }}" wire:click="$set('kategori', '{{ $item->slug }}')"
                    aria-pressed="{{ $kategori === $item->slug ? 'true' : 'false' }}"
                    @class(['chip', 'chip-on' => $kategori === $item->slug])>{{ $item->nama }}</button>
            @endforeach
        </div>
    @endif

    <section class="mt-6">
        <div class="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-5 xl:grid-cols-4">
            @forelse ($produks as $produk)
                <x-product-card :produk="$produk" wire:key="produk-{{ $produk->id }}" />
            @empty
                <div class="card col-span-full">
                    <x-empty icon="search">
                        @if ($cari)
                            Tidak ada produk yang cocok dengan “{{ $cari }}”. Coba kata lain atau lihat semua produk.
                        @elseif ($kategori)
                            Belum ada produk di kategori ini.
                        @else
                            Belum ada produk. Produk baru akan tampil di sini begitu ditambahkan.
                        @endif
                        @if ($cari || $kategori)
                            <x-slot:action>
                                <button type="button" wire:click="semuaProduk" class="btn btn-secondary">Tampilkan semua produk</button>
                            </x-slot:action>
                        @endif
                    </x-empty>
                </div>
            @endforelse
        </div>

        <div class="mt-10">
            {{ $produks->links() }}
        </div>
    </section>
</div>
