{{-- Badge status pesanan ($status: App\Enums\StatusPesanan) --}}
@props(['status'])

<span {{ $attributes->class(['badge', $status->badge()]) }}>{{ $status->label() }}</span>
