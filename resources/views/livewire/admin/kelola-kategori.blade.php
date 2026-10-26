<div>
    <x-admin-heading title="Kategori" subtitle="Kelompokkan produk supaya pelanggan cepat menemukan yang dicari." />

    <x-flash class="mt-6" />

    <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px] lg:items-start">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Kategori</th>
                        <th class="text-right">Urutan</th>
                        <th class="text-right">Produk</th>
                        <th>Status</th>
                        <th><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($kategoris as $kategori)
                        <tr wire:key="kategori-{{ $kategori->id }}">
                            <td>
                                <p class="font-medium">{{ $kategori->nama }}</p>
                                <p class="text-sm text-ink-muted">/{{ $kategori->slug }}</p>
                            </td>
                            <td class="text-right tabular-nums">{{ $kategori->urutan }}</td>
                            <td class="text-right tabular-nums">{{ $kategori->produks_count }}</td>
                            <td>
                                @if ($kategori->aktif)
                                    <span class="badge badge-ok">Aktif</span>
                                @else
                                    <span class="badge badge-out">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <button type="button" wire:click="ubah({{ $kategori->id }})" class="btn btn-secondary btn-sm">
                                    <x-icon name="pencil" /> Ubah
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5"><x-empty icon="package">Belum ada kategori. Tambahkan yang pertama lewat formulir di samping.</x-empty></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <form wire:submit="simpan" class="card flex flex-col gap-5 p-6 lg:sticky lg:top-8">
            <h2 class="text-xl">{{ $form->kategori ? 'Ubah kategori' : 'Tambah kategori' }}</h2>
            <div>
                <label for="kategori-nama" class="label">Nama</label>
                <input id="kategori-nama" type="text" wire:model="form.nama" required placeholder="mis. Hampers"
                    class="input @error('form.nama') input-invalid @enderror">
                @error('form.nama') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="kategori-slug" class="label">Slug</label>
                <input id="kategori-slug" type="text" wire:model="form.slug" aria-describedby="kategori-slug-petunjuk"
                    class="input @error('form.slug') input-invalid @enderror">
                <p id="kategori-slug-petunjuk" class="hint">Dipakai di URL. Kosongkan untuk dibuat dari nama.</p>
                @error('form.slug') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="kategori-urutan" class="label">Urutan</label>
                <input id="kategori-urutan" type="number" min="0" wire:model="form.urutan" required
                    class="input @error('form.urutan') input-invalid @enderror">
                @error('form.urutan') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <label class="flex items-center gap-2.5 text-[15px]">
                <input type="checkbox" wire:model="form.aktif" class="size-4 accent-brand"> Aktif
            </label>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                    <x-icon name="check" /> Simpan
                </button>
                @if ($form->kategori)
                    <button type="button" wire:click="batal" class="btn btn-secondary">Batal</button>
                @endif
            </div>
        </form>
    </div>
</div>
