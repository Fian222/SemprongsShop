<div class="wrap pt-4">
    <x-breadcrumb :items="['Beranda' => route('home'), 'Semua semprong' => null]" />

    <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-4xl md:text-5xl">Semua semprong</h1>
            <p class="mt-1 text-ink-muted">
                @if ($search)
                    {{ $barangs->total() }} hasil untuk “{{ $search }}”
                @else
                    {{ $barangs->total() }} produk, dibuat setelah dipesan.
                @endif
            </p>
        </div>
        <label class="relative block sm:w-80">
            <span class="sr-only">Cari produk</span>
            <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3.5 size-5 -translate-y-1/2 text-ink-muted" />
            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Cari rasa" class="input h-11 pl-11">
        </label>
    </div>

    <section class="mt-6">
        <div class="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-5 xl:grid-cols-4">
            @forelse ($barangs as $barang)
                <x-product-card :barang="$barang" :href="route('produk.detail', $barang->id)" wire:key="barang-{{ $barang->id }}" />
            @empty
                <div class="card col-span-full">
                    <x-empty icon="search">
                        Tidak ada rasa yang cocok dengan “{{ $search }}”. Coba kata lain atau lihat semua rasa.
                        <x-slot:action>
                            <button type="button" wire:click="$set('search', '')" class="btn btn-secondary">Tampilkan semua rasa</button>
                        </x-slot:action>
                    </x-empty>
                </div>
            @endforelse
        </div>

        <div class="mt-10">
            {{ $barangs->links() }}
        </div>
    </section>
</div>
