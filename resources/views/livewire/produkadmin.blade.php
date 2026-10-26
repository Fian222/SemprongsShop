<div>
    <x-admin-heading title="Produk" subtitle="Kelola katalog, stok, dan harga.">
        <div class="flex flex-col gap-3 sm:flex-row">
            <label class="relative block sm:w-64">
                <span class="sr-only">Cari produk</span>
                <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3.5 size-5 -translate-y-1/2 text-ink-muted" />
                <input wire:model.live.debounce.300ms="search" type="search" placeholder="Cari produk" class="input h-11 bg-surface pl-11">
            </label>
            <a wire:navigate href="{{ route('admin.tambah') }}" class="btn btn-primary">
                <x-icon name="plus" /> Tambah barang
            </a>
        </div>
    </x-admin-heading>

    <div class="table-wrap mt-8">
        <table class="table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th class="text-right">Harga</th>
                    <th class="text-right">Stok</th>
                    <th><span class="sr-only">Aksi</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($barangs as $barang)
                    <tr wire:key="barang-{{ $barang->id }}">
                        <td>
                            <div class="flex items-center gap-3">
                                <img src="{{ url('build/images') }}/{{ $barang->gambar }}" alt=""
                                    class="size-12 shrink-0 rounded-xs bg-surface-muted object-cover">
                                <span class="font-medium">{{ $barang->nama_barang }}</span>
                            </div>
                        </td>
                        <td class="text-right font-medium whitespace-nowrap tabular-nums">@rupiah($barang->harga_barang)</td>
                        <td class="text-right whitespace-nowrap">
                            @if ($barang->habis())
                                <span class="badge badge-out">Habis</span>
                            @elseif ($barang->menipis())
                                <span class="badge badge-low">{{ $barang->stok_barang }}</span>
                            @else
                                <span class="tabular-nums">{{ $barang->stok_barang }}</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <a wire:navigate href="{{ route('admin.detail', $barang->id) }}" class="btn btn-secondary btn-sm">
                                <x-icon name="pencil" /> Ubah stok & harga
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4"><x-empty icon="package">Tidak ada produk yang cocok. Ubah kata pencarian atau tambah barang baru.</x-empty></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-8">
        {{ $barangs->links() }}
    </div>
</div>
