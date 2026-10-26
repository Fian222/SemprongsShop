{{-- Daftar Item pesanan dengan foto dan harga; $sku untuk tampilan admin --}}
@props(['pesanan', 'sku' => false])

<section {{ $attributes->class('card') }} aria-labelledby="judul-barang">
    <h2 id="judul-barang" class="border-b border-line px-6 py-5 text-xl">Isi pesanan</h2>
    <ul class="divide-y divide-line">
        @foreach ($pesanan->items as $item)
            <li wire:key="item-{{ $item->id }}" class="flex items-center gap-4 px-6 py-4">
                @if ($item->fotoUrl())
                    <img src="{{ $item->fotoUrl() }}" alt="" class="size-14 shrink-0 rounded-xs bg-surface-muted object-cover">
                @else
                    <x-foto-produk :foto="null" class="size-14 shrink-0 rounded-xs" />
                @endif
                <div class="min-w-0 flex-1 text-[15px]">
                    <p class="truncate font-medium">{{ $item->nama_produk }}</p>
                    <p class="text-sm text-ink-muted tabular-nums">{{ $item->nama_varian }}{{ $sku ? ' · '.$item->sku : '' }} · {{ $item->jumlah }} × @rupiah($item->harga)</p>
                </div>
                <p class="price whitespace-nowrap">@rupiah($item->jumlahHarga())</p>
            </li>
        @endforeach
    </ul>
</section>
