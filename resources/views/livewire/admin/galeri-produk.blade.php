<section class="card" aria-labelledby="judul-galeri">
    <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line px-6 py-5">
        <div>
            <h2 id="judul-galeri" class="text-xl">Foto produk</h2>
            <p class="mt-1 text-sm text-ink-muted">Foto pertama menjadi foto utama di kartu produk. Geser untuk mengubah urutan.</p>
        </div>
        <x-flash />
    </header>

    @if ($fotos->isEmpty())
        <x-empty icon="image-plus">Belum ada foto. Produk tanpa foto tampil dengan bidang polos di toko.</x-empty>
    @else
        <ol class="grid grid-cols-2 gap-4 p-6 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($fotos as $foto)
                <li wire:key="foto-{{ $foto->id }}" class="flex flex-col gap-2">
                    <div class="relative">
                        <x-foto-produk :foto="$foto" alt="Foto {{ $loop->iteration }}" class="aspect-square w-full rounded-xs bg-surface-muted" />
                        @if ($loop->first)
                            <span class="badge badge-low absolute top-2 left-2">Utama</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex">
                            <button type="button" wire:click="geser({{ $foto->id }}, -1)" @disabled($loop->first)
                                class="btn btn-icon size-9 text-ink-muted hover:bg-surface-muted hover:text-ink" aria-label="Majukan foto {{ $loop->iteration }}">
                                <x-icon name="arrow-left" />
                            </button>
                            <button type="button" wire:click="geser({{ $foto->id }}, 1)" @disabled($loop->last)
                                class="btn btn-icon size-9 text-ink-muted hover:bg-surface-muted hover:text-ink" aria-label="Mundurkan foto {{ $loop->iteration }}">
                                <x-icon name="arrow-right" />
                            </button>
                        </div>
                        <button type="button" wire:click="hapus({{ $foto->id }})" wire:confirm="Hapus foto ini?"
                            class="btn btn-danger btn-icon size-9" aria-label="Hapus foto {{ $loop->iteration }}">
                            <x-icon name="trash" />
                        </button>
                    </div>
                </li>
            @endforeach
        </ol>
    @endif

    <form wire:submit="unggah" class="flex flex-col gap-3 border-t border-line p-6">
        <label for="foto-unggahan" class="label mb-0">Tambah foto</label>
        <input id="foto-unggahan" type="file" accept="image/*" multiple wire:model="unggahan" aria-describedby="foto-unggahan-petunjuk"
            class="input @error('unggahan') input-invalid @enderror @error('unggahan.*') input-invalid @enderror">
        <p id="foto-unggahan-petunjuk" class="hint mt-0">JPG, PNG, atau WebP, maksimal 2 MB per foto, 10 foto sekaligus.</p>
        @error('unggahan') <p class="field-error">{{ $message }}</p> @enderror
        @foreach ($errors->get('unggahan.*') as $pesan)
            <p class="field-error">{{ $pesan[0] }}</p>
        @endforeach
        <div wire:loading wire:target="unggahan" class="text-sm text-ink-muted">Mengunggah…</div>
        <button type="submit" class="btn btn-primary self-start" wire:loading.attr="disabled" wire:target="unggahan,unggah">
            <x-icon name="image-plus" /> Simpan foto
        </button>
    </form>
</section>
