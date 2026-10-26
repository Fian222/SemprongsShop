<div class="flex flex-col gap-8">
    <div>
        <a wire:navigate href="{{ route('admin.produk') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-ink-muted hover:text-ink">
            <x-icon name="arrow-left" /> Kembali ke produk
        </a>
        <h1 class="mt-4 text-4xl leading-tight">{{ $form->produk ? $form->produk->nama : 'Tambah produk' }}</h1>
        @if ($form->produk?->aktif)
            <a href="{{ route('produk.detail', $form->produk->slug) }}" target="_blank" class="link mt-1 inline-block text-sm">Lihat di toko</a>
        @endif
    </div>

    <x-flash />

    <form wire:submit="simpan" class="card flex flex-col gap-5 p-6 sm:p-8">
        <h2 class="text-xl">Detail produk</h2>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="produk-nama" class="label">Nama</label>
                <input id="produk-nama" type="text" wire:model="form.nama" required placeholder="mis. Semprong Pandan"
                    class="input @error('form.nama') input-invalid @enderror">
                @error('form.nama') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="produk-kategori" class="label">Kategori</label>
                <select id="produk-kategori" wire:model="form.kategori_id" required
                    class="input @error('form.kategori_id') input-invalid @enderror">
                    <option value="">Pilih kategori</option>
                    @foreach ($kategoris as $kategori)
                        <option value="{{ $kategori->id }}">{{ $kategori->nama }}{{ $kategori->aktif ? '' : ' (nonaktif)' }}</option>
                    @endforeach
                </select>
                @error('form.kategori_id') <p class="field-error">{{ $message }}</p> @enderror
                @if ($kategoris->isEmpty())
                    <p class="hint">Belum ada kategori. <a wire:navigate href="{{ route('admin.kategori') }}" class="link">Buat kategori dulu</a>.</p>
                @endif
            </div>
        </div>

        <div>
            <label for="produk-slug" class="label">Slug</label>
            <input id="produk-slug" type="text" wire:model="form.slug" aria-describedby="produk-slug-petunjuk"
                class="input @error('form.slug') input-invalid @enderror">
            <p id="produk-slug-petunjuk" class="hint">Alamat halaman produk, /produk/slug. Kosongkan untuk dibuat dari nama.</p>
            @error('form.slug') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="produk-deskripsi" class="label">Deskripsi</label>
            <textarea id="produk-deskripsi" rows="4" wire:model="form.deskripsi" required
                class="input h-auto py-3 @error('form.deskripsi') input-invalid @enderror"></textarea>
            @error('form.deskripsi') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="produk-komposisi" class="label">Komposisi</label>
            <textarea id="produk-komposisi" rows="2" wire:model="form.komposisi" aria-describedby="produk-komposisi-petunjuk"
                class="input h-auto py-3 @error('form.komposisi') input-invalid @enderror"></textarea>
            <p id="produk-komposisi-petunjuk" class="hint">Bahan-bahan, termasuk yang bisa memicu alergi.</p>
            @error('form.komposisi') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:gap-8">
            <label class="flex items-center gap-2.5 text-[15px]">
                <input type="checkbox" wire:model="form.aktif" class="size-4 accent-brand"> Aktif
            </label>
            <label class="flex items-center gap-2.5 text-[15px]">
                <input type="checkbox" wire:model="form.unggulan" class="size-4 accent-brand"> Produk unggulan di beranda
            </label>
        </div>

        <button type="submit" class="btn btn-primary self-start" wire:loading.attr="disabled">
            <x-icon name="check" /> {{ $form->produk ? 'Simpan perubahan' : 'Buat produk' }}
        </button>
    </form>

    @if ($form->produk)
        <livewire:admin.varian-produk :produk="$form->produk" />
        <livewire:admin.galeri-produk :produk="$form->produk" />
    @endif
</div>
