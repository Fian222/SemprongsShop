{{-- Kurir, layanan, estimasi, dan berat Pengiriman --}}
@props(['pengiriman'])

<p {{ $attributes->class('flex flex-wrap items-center gap-x-2 text-[15px]') }}>
    <x-icon name="truck" class="size-5 text-brand" />
    <span class="font-medium">{{ $pengiriman->label() }}</span>
    <span class="text-ink-muted">· estimasi {{ $pengiriman->estimasi }} · {{ number_format($pengiriman->berat / 1000, 1, ',', '.') }} kg</span>
</p>
