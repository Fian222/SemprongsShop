<div class="max-w-2xl">
    <a wire:navigate href="{{ route('admin.produk') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-ink-muted hover:text-ink">
        <x-icon name="arrow-left" /> Kembali ke produk
    </a>

    <h1 class="mt-4 text-4xl leading-tight">Tambah barang</h1>

    <x-flash class="mt-6" />

    <form wire:submit="tambah" class="card mt-6 flex flex-col gap-5 p-6 sm:p-8">
        <div>
            <label for="nama" class="label">Nama barang</label>
            <input type="text" id="nama" required wire:model="nama" placeholder="mis. Semprong Pandan"
                class="input @error('nama') input-invalid @enderror">
            @error('nama')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="harga" class="label">Harga (Rp)</label>
                <input type="number" id="harga" min="0" required wire:model="harga"
                    class="input @error('harga') input-invalid @enderror">
                @error('harga')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="stok" class="label">Stok</label>
                <input type="number" id="stok" min="0" required wire:model="stok"
                    class="input @error('stok') input-invalid @enderror">
                @error('stok')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="gambar" class="label">Foto produk</label>
            <input type="file" id="gambar" accept="image/*" required wire:model="gambar"
                class="input @error('gambar') input-invalid @enderror">
            <p class="hint">JPG atau PNG, maksimal 1 MB.</p>
            @error('gambar')
                <p class="field-error">{{ $message }}</p>
            @enderror
            <div wire:loading wire:target="gambar" class="mt-2 text-sm text-ink-muted">Mengunggah…</div>
            @if ($gambar?->isPreviewable())
                <img src="{{ $gambar->temporaryUrl() }}" alt="Pratinjau" class="mt-3 size-32 rounded-xs bg-surface-muted object-cover">
            @endif
        </div>

        <button type="submit" class="btn btn-primary self-start" wire:loading.attr="disabled">
            <x-icon name="plus" /> Simpan barang
        </button>
    </form>
</div>
