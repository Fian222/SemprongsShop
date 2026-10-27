<x-mail::message>
# Pesananmu dalam perjalanan

Halo {{ $pesanan->user->name }}, pesanan **{{ $pesanan->nomor }}** sudah kami serahkan ke kurir.

<x-mail::panel>
Kurir: **{{ $pesanan->pengiriman->label() }}**<br>
Nomor resi: **{{ $pesanan->pengiriman->resi }}**@if ($pesanan->pengiriman->estimasi)<br>
Estimasi tiba: {{ $pesanan->pengiriman->estimasi }}@endif
</x-mail::panel>

Kamu bisa melacak perjalanan paketnya di halaman pesanan.

<x-mail::button :url="route('pesanan.detail', $pesanan)">
Lacak paket
</x-mail::button>

Salam hangat,<br>
{{ config('app.name') }}
</x-mail::message>
