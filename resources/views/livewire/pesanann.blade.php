<div class="wrap pt-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <x-breadcrumb :items="['Beranda' => route('home'), 'Tas belanja' => null]" />
        @unless (blank($pesanan_details))
            <x-langkah-pesan :aktif="1" />
        @endunless
    </div>

    <h1 class="mt-4 text-4xl md:text-5xl">Tas belanja</h1>

    <x-flash type="danger" class="mt-6" />

    @if (blank($pesanan_details))
        <div class="card mt-6">
            <x-empty icon="shopping-bag">
                Tas belanjamu masih kosong. Pilih rasa dulu, lalu tentukan jumlahnya.
                <x-slot:action>
                    <a wire:navigate href="{{ route('produk') }}" class="btn btn-primary">Pilih rasa</a>
                </x-slot:action>
            </x-empty>
        </div>
    @else
        <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_360px] lg:items-start">
            <ul class="card divide-y divide-line">
                @foreach ($pesanan_details as $pesan)
                    <li wire:key="detail-{{ $pesan->id }}" class="flex items-center gap-4 p-4">
                        <x-foto-produk :foto="$pesan->varian->produk->fotoUtama" class="size-20 shrink-0 rounded-xs bg-surface-muted" />
                        <div class="min-w-0 flex-1">
                            <h2 class="truncate text-[15px] font-medium tracking-normal">
                                <a wire:navigate href="{{ route('produk.detail', $pesan->varian->produk->slug) }}" class="hover:underline">{{ $pesan->varian->produk->nama }}</a>
                            </h2>
                            <p class="mt-0.5 text-sm text-ink-muted tabular-nums">
                                {{ $pesan->varian->nama }} · {{ $pesan->jumlah_pesanan }} × @rupiah($pesan->varian->harga)
                            </p>
                            <p class="price mt-1.5 text-base">@rupiah($pesan->total_harga)</p>
                        </div>
                        <button type="button" wire:click="destroy({{ $pesan->id }})" wire:loading.attr="disabled"
                            class="btn btn-danger btn-icon" aria-label="Hapus {{ $pesan->varian->produk->nama }} dari tas belanja">
                            <x-icon name="trash" class="size-[18px]" />
                        </button>
                    </li>
                @endforeach
            </ul>

            @if (!empty($pesanan))
                <aside class="panel p-6 lg:sticky lg:top-36">
                    <h2 class="text-lg">Ringkasan</h2>
                    <dl class="mt-4 flex flex-col gap-2 text-[15px]">
                        <div class="flex justify-between">
                            <dt class="text-ink-muted">{{ count($pesanan_details) }} barang</dt>
                            <dd class="tabular-nums">@rupiah($pesanan->total_harga)</dd>
                        </div>
                        <div class="mt-2 flex items-baseline justify-between border-t border-line pt-3">
                            <dt class="font-medium">Total</dt>
                            <dd class="price text-2xl">@rupiah($pesanan->total_harga)</dd>
                        </div>
                    </dl>
                    <a wire:navigate href="{{ route('checkout') }}" class="btn btn-primary mt-5 w-full">Lanjut ke checkout</a>
                    <a wire:navigate href="{{ route('produk') }}" class="btn btn-secondary mt-2 w-full">Tambah rasa lain</a>
                </aside>
            @endif
        </div>
    @endif
</div>
