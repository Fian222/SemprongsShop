{{-- Subtotal, ongkir, diskon, dan total Pesanan; $slot untuk baris tambahan di bawah total --}}
@props(['pesanan'])

<dl {{ $attributes->class('flex flex-col gap-2 text-[15px]') }}>
    <div class="flex justify-between">
        <dt class="text-ink-muted">Subtotal</dt>
        <dd class="tabular-nums">@rupiah($pesanan->subtotal)</dd>
    </div>
    <div class="flex justify-between">
        <dt class="text-ink-muted">Ongkir{{ $pesanan->pengiriman ? ' '.$pesanan->pengiriman->nama_kurir : '' }}</dt>
        <dd class="tabular-nums">@rupiah($pesanan->ongkir)</dd>
    </div>
    @if ($pesanan->diskon)
        <div class="flex justify-between">
            <dt class="text-ink-muted">Diskon</dt>
            <dd class="tabular-nums text-ok">−@rupiah($pesanan->diskon)</dd>
        </div>
    @endif
    <div class="mt-2 flex items-baseline justify-between border-t border-line pt-3">
        <dt class="font-medium">Total</dt>
        <dd class="price text-[28px] leading-none">@rupiah($pesanan->total)</dd>
    </div>
    {{ $slot }}
</dl>
