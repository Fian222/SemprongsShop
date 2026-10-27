<x-mail::message>
# Pembayaran diterima

Halo {{ $pesanan->user->name }}, terima kasih. Pembayaran untuk pesanan **{{ $pesanan->nomor }}** sudah kami terima, dan pesananmu segera kami siapkan. Invoice-nya kami lampirkan di email ini.

<x-mail::table>
| Produk | Jumlah | Total |
|:-------|:------:|------:|
@foreach ($pesanan->items as $item)
| {{ $item->nama_produk }} · {{ $item->nama_varian }} | {{ $item->jumlah }} | @rupiah($item->jumlahHarga()) |
@endforeach
| Ongkir{{ $pesanan->pengiriman ? ' '.$pesanan->pengiriman->label() : '' }} | | @rupiah($pesanan->ongkir) |
@if ($pesanan->diskon)
| Diskon{{ $pesanan->voucher_kode ? ' '.$pesanan->voucher_kode : '' }} | | −@rupiah($pesanan->diskon) |
@endif
| **Total** | | **@rupiah($pesanan->total)** |
</x-mail::table>

<x-mail::button :url="route('pesanan.detail', $pesanan)">
Lihat pesanan
</x-mail::button>

Salam hangat,<br>
{{ config('app.name') }}
</x-mail::message>
