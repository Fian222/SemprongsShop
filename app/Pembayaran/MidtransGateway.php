<?php

namespace App\Pembayaran;

use App\Models\Pesanan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

// Adapter Midtrans Snap: https://docs.midtrans.com/reference/backend-integration
class MidtransGateway implements PaymentGateway
{
    public function __construct(private string $serverKey, private string $clientKey, private bool $produksi) {}

    public function nama(): string
    {
        return 'midtrans';
    }

    public function buatTransaksi(Pesanan $pesanan, string $referensi): TransaksiBaru
    {
        if ($this->serverKey === '') {
            throw new RuntimeException('MIDTRANS_SERVER_KEY belum diisi.');
        }

        $pesanan->loadMissing('items', 'user');
        $alamat = $pesanan->alamat;

        $respons = Http::withBasicAuth($this->serverKey, '')
            ->acceptJson()
            ->timeout(15)
            ->post($this->urlSnap().'/snap/v1/transactions', [
                'transaction_details' => [
                    'order_id' => $referensi,
                    'gross_amount' => $pesanan->total,
                ],
                // jumlah harga item harus sama dengan gross_amount
                'item_details' => $pesanan->items->map(fn ($item) => [
                    'id' => $item->sku,
                    'price' => $item->harga,
                    'quantity' => $item->jumlah,
                    'name' => Str::limit($item->nama_produk.' '.$item->nama_varian, 50, ''),
                ])->all(),
                'customer_details' => [
                    'first_name' => $pesanan->user->name,
                    'email' => $pesanan->user->email,
                    'phone' => $alamat['no_hp'],
                    'shipping_address' => [
                        'first_name' => $alamat['penerima'],
                        'phone' => $alamat['no_hp'],
                        'address' => $alamat['alamat'],
                        'postal_code' => $alamat['kode_pos'],
                        'country_code' => 'IDN',
                    ],
                ],
                // jendela Snap tutup bersamaan dengan batas bayar pesanan
                'expiry' => [
                    'start_time' => $pesanan->created_at->format('Y-m-d H:i:s O'),
                    'unit' => 'minute',
                    'duration' => (int) $pesanan->created_at->diffInMinutes($pesanan->batas_bayar),
                ],
                'callbacks' => [
                    'finish' => route('pesanan.detail', $pesanan),
                ],
            ]);

        if ($respons->failed() || ! $respons->json('token')) {
            throw new RuntimeException('Midtrans menolak transaksi: '.$respons->body());
        }

        return new TransaksiBaru($respons->json('token'), $respons->json('redirect_url'));
    }

    public function bacaNotifikasi(array $payload): Notifikasi
    {
        return (new NotifikasiMidtrans($this->serverKey))->baca($payload);
    }

    public function skripJendela(): array
    {
        return ['src' => $this->urlSnap().'/snap/snap.js', 'data-client-key' => $this->clientKey];
    }

    private function urlSnap(): string
    {
        return $this->produksi ? 'https://app.midtrans.com' : 'https://app.sandbox.midtrans.com';
    }
}
