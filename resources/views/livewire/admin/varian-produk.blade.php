<section class="card" aria-labelledby="judul-varian">
    <header class="border-b border-line px-6 py-5">
        <h2 id="judul-varian" class="text-xl">Varian</h2>
        <p class="mt-1 text-sm text-ink-muted">Setiap kemasan punya harga, stok, dan berat sendiri. Produk baru tampil di toko setelah punya varian aktif.</p>
    </header>

    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>Kemasan</th>
                    <th class="text-right">Harga</th>
                    <th class="text-right">Stok</th>
                    <th class="text-right">Berat</th>
                    <th>Status</th>
                    <th><span class="sr-only">Aksi</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($varians as $varian)
                    <tr wire:key="varian-{{ $varian->id }}" @class(['bg-surface-muted' => $form->varian?->is($varian)])>
                        <td>
                            <p class="font-medium">{{ $varian->nama }}</p>
                            <p class="text-sm text-ink-muted">{{ $varian->sku }}</p>
                        </td>
                        <td class="text-right whitespace-nowrap tabular-nums">
                            <span class="font-medium">@rupiah($varian->harga)</span>
                            @if ($varian->punyaHargaCoret())
                                <s class="block text-sm text-ink-muted">@rupiah($varian->harga_coret)</s>
                            @endif
                        </td>
                        <td class="text-right whitespace-nowrap">
                            @if ($varian->habis())
                                <span class="badge badge-out">Habis</span>
                            @elseif ($varian->menipis())
                                <span class="badge badge-low">{{ $varian->stok }}</span>
                            @else
                                <span class="tabular-nums">{{ $varian->stok }}</span>
                            @endif
                        </td>
                        <td class="text-right whitespace-nowrap tabular-nums">{{ $varian->berat }} g</td>
                        <td>
                            @if ($varian->aktif)
                                <span class="badge badge-ok">Aktif</span>
                            @else
                                <span class="badge badge-out">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <button type="button" wire:click="ubah({{ $varian->id }})" class="btn btn-secondary btn-sm"
                                aria-label="Ubah varian {{ $varian->nama }}">
                                <x-icon name="pencil" /> Ubah
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6"><x-empty icon="package">Belum ada varian. Tambahkan varian pertama di bawah.</x-empty></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <form wire:submit="simpan" class="flex flex-col gap-5 border-t border-line p-6">
        <div class="flex items-center justify-between gap-3">
            <h3 class="text-lg">{{ $form->varian ? 'Ubah varian '.$form->varian->nama : 'Tambah varian' }}</h3>
            <x-flash />
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="varian-nama" class="label">Nama kemasan</label>
                <input id="varian-nama" type="text" wire:model="form.nama" required placeholder="mis. Toples 250 g"
                    class="input @error('form.nama') input-invalid @enderror">
                @error('form.nama') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="varian-sku" class="label">SKU</label>
                <input id="varian-sku" type="text" wire:model="form.sku" required placeholder="mis. SMP-PDN-250"
                    class="input not-placeholder-shown:uppercase @error('form.sku') input-invalid @enderror">
                @error('form.sku') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="varian-harga" class="label">Harga (Rp)</label>
                <input id="varian-harga" type="number" min="1" wire:model="form.harga" required
                    class="input @error('form.harga') input-invalid @enderror">
                @error('form.harga') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="varian-harga-coret" class="label">Harga coret (Rp, opsional)</label>
                <input id="varian-harga-coret" type="number" min="1" wire:model="form.harga_coret" aria-describedby="varian-harga-coret-petunjuk"
                    class="input @error('form.harga_coret') input-invalid @enderror">
                <p id="varian-harga-coret-petunjuk" class="hint">Harga sebelum diskon, ditampilkan tercoret di samping harga.</p>
                @error('form.harga_coret') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="varian-stok" class="label">Stok</label>
                <input id="varian-stok" type="number" min="0" wire:model="form.stok" required
                    class="input @error('form.stok') input-invalid @enderror">
                @error('form.stok') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="varian-berat" class="label">Berat (gram)</label>
                <input id="varian-berat" type="number" min="1" wire:model="form.berat" required aria-describedby="varian-berat-petunjuk"
                    class="input @error('form.berat') input-invalid @enderror">
                <p id="varian-berat-petunjuk" class="hint">Berat beserta kemasan, untuk menghitung ongkir.</p>
                @error('form.berat') <p class="field-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <label class="flex items-center gap-2.5 text-[15px]">
            <input type="checkbox" wire:model="form.aktif" class="size-4 accent-brand"> Aktif
        </label>

        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                <x-icon name="check" /> {{ $form->varian ? 'Simpan varian' : 'Tambah varian' }}
            </button>
            @if ($form->varian)
                <button type="button" wire:click="batal" class="btn btn-secondary">Batal</button>
            @endif
        </div>
    </form>
</section>
