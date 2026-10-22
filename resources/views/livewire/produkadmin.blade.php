<div>
    <x-admin-heading title="Produk" subtitle="Kelola katalog, stok, dan harga.">
        <div class="flex flex-col gap-3 sm:flex-row">
            <label class="relative block sm:w-64">
                <span class="sr-only">Cari produk</span>
                <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-ink-muted" />
                <input wire:model.live.debounce.300ms="search" type="search" placeholder="Cari produk…" class="input rounded-full pl-10">
            </label>
            <a wire:navigate href="{{ route('admin.tambah') }}" class="btn btn-primary">
                <x-icon name="plus" /> Tambah barang
            </a>
        </div>
    </x-admin-heading>

    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($barangs as $barang)
            <x-product-card :barang="$barang" :href="route('admin.detail', $barang->id)" action="Ubah stok & harga"
                wire:key="barang-{{ $barang->id }}" />
        @empty
            <div class="card sm:col-span-2 lg:col-span-3">
                <x-empty icon="package">Tidak ada produk ditemukan.</x-empty>
            </div>
        @endforelse
    </div>

    <div class="mt-10">
        {{ $barangs->links() }}
    </div>
</div>
