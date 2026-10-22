<div class="mx-auto max-w-6xl px-4 pt-8 sm:px-6">
    <x-breadcrumb :items="['Beranda' => route('home'), 'Pesanan' => null]" />

    <h1 class="mt-6 text-4xl font-bold">Pesanan kamu</h1>

    <x-flash type="danger" class="mt-6" />

    @if (blank($pesanan_details))
        <div class="card mt-8">
            <x-empty icon="shopping-bag">
                Belum ada kue di pesananmu.
                <a wire:navigate href="{{ route('produk') }}" class="font-semibold text-brand hover:underline">Pilih produk</a>
            </x-empty>
        </div>
    @else
        <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_22rem]">
            <ul class="flex flex-col gap-4">
                @foreach ($pesanan_details as $pesan)
                    <li wire:key="detail-{{ $pesan->id }}" class="card flex items-center gap-4 p-4">
                        <img src="{{ url('build/images') }}/{{ $pesan->barang->gambar }}" alt="{{ $pesan->barang->nama_barang }}"
                            class="size-20 shrink-0 rounded-xl object-cover sm:size-24">
                        <div class="min-w-0 flex-1">
                            <h2 class="truncate font-sans text-base font-semibold">{{ $pesan->barang->nama_barang }}</h2>
                            <p class="mt-0.5 text-sm text-ink-muted">
                                {{ $pesan->jumlah_pesanan }} × @rupiah($pesan->barang->harga_barang)
                            </p>
                            <p class="mt-2 font-semibold">@rupiah($pesan->total_harga)</p>
                        </div>
                        <button type="button" wire:click="destroy({{ $pesan->id }})"
                            class="btn btn-danger size-10 p-0" aria-label="Hapus {{ $pesan->barang->nama_barang }}">
                            <x-icon name="trash" />
                        </button>
                    </li>
                @endforeach
            </ul>

            @if (!empty($pesanan))
                <aside class="card h-fit p-6 lg:sticky lg:top-24">
                    <h2 class="text-xl font-bold">Ringkasan</h2>
                    <dl class="mt-4 flex items-center justify-between border-t border-line pt-4">
                        <dt class="text-ink-muted">Total yang harus dibayar</dt>
                        <dd class="text-xl font-bold text-brand">@rupiah($pesanan->total_harga)</dd>
                    </dl>
                    <a wire:navigate href="{{ route('checkout') }}" class="btn btn-primary mt-6 w-full">
                        Lanjut ke checkout <x-icon name="arrow-right" />
                    </a>
                </aside>
            @endif
        </div>
    @endif
</div>
