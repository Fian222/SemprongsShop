<div>
    <x-admin-heading title="Produk" subtitle="Kelola katalog, varian, dan foto.">
        <div class="flex flex-col gap-3 sm:flex-row">
            <label class="relative block sm:w-64">
                <span class="sr-only">Cari produk</span>
                <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3.5 size-5 -translate-y-1/2 text-ink-muted" />
                <input wire:model.live.debounce.300ms="cari" type="search" placeholder="Cari produk" class="input h-11 bg-surface pl-11">
            </label>
            <a wire:navigate href="{{ route('admin.produk.baru') }}" class="btn btn-primary">
                <x-icon name="plus" /> Tambah produk
            </a>
        </div>
    </x-admin-heading>

    <x-flash class="mt-6" />

    <div class="table-wrap mt-8">
        <table class="table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th class="text-right">Varian aktif</th>
                    <th class="text-right">Harga mulai</th>
                    <th class="text-right">Stok</th>
                    <th>Status</th>
                    <th><span class="sr-only">Aksi</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($produks as $produk)
                    <tr wire:key="produk-{{ $produk->id }}">
                        <td>
                            <div class="flex items-center gap-3">
                                <x-foto-produk :foto="$produk->fotoUtama" class="size-12 shrink-0 rounded-xs bg-surface-muted" />
                                <div>
                                    <p class="font-medium">{{ $produk->nama }}</p>
                                    <p class="text-sm text-ink-muted">{{ $produk->kategori->nama }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="text-right tabular-nums">{{ $produk->varian_aktif_count }}</td>
                        <td class="text-right font-medium whitespace-nowrap tabular-nums">
                            {{ $produk->harga_mulai === null ? '–' : \App\Support\Format::rupiah($produk->harga_mulai) }}
                        </td>
                        <td class="text-right tabular-nums">{{ (int) $produk->stok_tersedia }}</td>
                        <td>
                            <div class="flex flex-wrap gap-1">
                                @if (! $produk->aktif)
                                    <span class="badge badge-out">Nonaktif</span>
                                @elseif (! $produk->varian_aktif_count)
                                    <span class="badge badge-warn">Belum ada varian</span>
                                @else
                                    <span class="badge badge-ok">Aktif</span>
                                @endif
                                @if ($produk->unggulan)
                                    <span class="badge badge-low">Unggulan</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-right">
                            <a wire:navigate href="{{ route('admin.produk.ubah', $produk) }}" class="btn btn-secondary btn-sm">
                                <x-icon name="pencil" /> Ubah
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6"><x-empty icon="package">Tidak ada produk yang cocok. Ubah kata pencarian atau tambah produk baru.</x-empty></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-8">
        {{ $produks->links() }}
    </div>
</div>
