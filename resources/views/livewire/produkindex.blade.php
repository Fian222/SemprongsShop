<div class="mx-auto max-w-6xl px-4 pt-8 sm:px-6">
    <x-breadcrumb :items="['Beranda' => route('home'), 'Produk' => null]" />

    <div class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-4xl font-bold">Semua produk</h1>
            <p class="mt-1 text-ink-muted">Pilih rasa favoritmu, semua dibuat fresh setelah dipesan.</p>
        </div>
        <label class="relative block sm:w-72">
            <span class="sr-only">Cari produk</span>
            <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-ink-muted" />
            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Cari rasa…" class="input rounded-full pl-10">
        </label>
    </div>

    <section class="mt-8">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($barangs as $barang)
                <x-product-card :barang="$barang" :href="route('produk.detail', $barang->id)" wire:key="barang-{{ $barang->id }}" />
            @empty
                <div class="card sm:col-span-2 lg:col-span-3">
                    <x-empty icon="search">Tidak ada produk yang cocok dengan “{{ $search }}”.</x-empty>
                </div>
            @endforelse
        </div>

        <div class="mt-10">
            {{ $barangs->links() }}
        </div>
    </section>
</div>
