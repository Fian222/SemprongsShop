{{-- Salinan alamat pengiriman yang disimpan di Pesanan --}}
@props(['pesanan'])

<div {{ $attributes->class('text-[15px]') }}>
    <p class="font-medium">{{ $pesanan->alamat['penerima'] }} <span class="font-normal text-ink-muted">· {{ $pesanan->alamat['label'] }}</span></p>
    <p class="text-ink-muted">{{ $pesanan->alamat['no_hp'] }}</p>
    <p class="text-ink-muted">{{ $pesanan->alamat['alamat'] }}, {{ $pesanan->alamat['kode_pos'] }}</p>
    @if ($pesanan->alamat['area_nama'] ?? null)
        <p class="text-ink-muted">{{ $pesanan->alamat['area_nama'] }}</p>
    @endif
</div>
