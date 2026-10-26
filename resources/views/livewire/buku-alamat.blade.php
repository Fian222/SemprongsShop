<div class="wrap max-w-4xl pt-4">
    <x-breadcrumb :items="['Beranda' => route('home'), 'Buku alamat' => null]" />

    <div class="mt-4 flex flex-wrap items-end justify-between gap-4">
        <h1 class="text-4xl md:text-5xl">Buku alamat</h1>
        @unless ($terbuka)
            <button type="button" wire:click="tambah" class="btn btn-primary">
                <x-icon name="plus" /> Tambah alamat
            </button>
        @endunless
    </div>

    <x-flash class="mt-6" />

    @if ($terbuka)
        <form wire:submit="simpan" class="card mt-6 flex flex-col gap-5 p-6 sm:p-8">
            <h2 class="text-xl">{{ $form->alamat ? 'Ubah alamat' : 'Alamat baru' }}</h2>
            <x-form-alamat :form="$form" />
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                    <x-icon name="check" /> Simpan alamat
                </button>
                <button type="button" wire:click="batal" class="btn btn-secondary">Batal</button>
            </div>
        </form>
    @endif

    <ul class="mt-6 grid gap-4 sm:grid-cols-2">
        @forelse ($alamats as $alamat)
            <li wire:key="alamat-{{ $alamat->id }}" @class(['card flex flex-col gap-3 p-5', 'border-ink' => $alamat->utama])>
                <div class="flex items-center gap-2">
                    <h2 class="font-sans text-[15px] font-medium tracking-normal">{{ $alamat->label }}</h2>
                    @if ($alamat->utama)
                        <span class="badge badge-low">Utama</span>
                    @endif
                </div>
                <div class="flex-1 text-[15px]">
                    <p class="font-medium">{{ $alamat->penerima }}</p>
                    <p class="text-ink-muted">{{ $alamat->no_hp }}</p>
                    <p class="mt-1 text-ink-muted">{{ $alamat->alamat }}, {{ $alamat->kode_pos }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" wire:click="ubah({{ $alamat->id }})" class="btn btn-secondary btn-sm">
                        <x-icon name="pencil" /> Ubah
                    </button>
                    @unless ($alamat->utama)
                        <button type="button" wire:click="jadikanUtama({{ $alamat->id }})" class="btn btn-secondary btn-sm">Jadikan utama</button>
                    @endunless
                    <button type="button" wire:click="hapus({{ $alamat->id }})" wire:confirm="Hapus alamat {{ $alamat->label }}?"
                        class="btn btn-danger btn-sm" aria-label="Hapus alamat {{ $alamat->label }}">
                        <x-icon name="trash" /> Hapus
                    </button>
                </div>
            </li>
        @empty
            @unless ($terbuka)
                <li class="card sm:col-span-2">
                    <x-empty icon="map-pin">Belum ada alamat. Simpan alamat supaya checkout berikutnya lebih cepat.</x-empty>
                </li>
            @endunless
        @endforelse
    </ul>
</div>
