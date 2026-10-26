<?php

namespace App\Pembayaran;

use App\Models\Pesanan;
use Illuminate\Support\Str;

/**
 * Pembayaran tanpa jaringan untuk pengembangan dan feature test. Jendela pembayarannya adalah halaman
 * simulasi lokal, dan notifikasinya berformat Midtrans yang ditandatangani kunci lokal.
 */
class SimulasiGateway implements PaymentGateway
{
    private NotifikasiMidtrans $notifikasi;

    public function __construct()
    {
        $this->notifikasi = new NotifikasiMidtrans('simulasi-lokal');
    }

    public function nama(): string
    {
        return 'simulasi';
    }

    public function buatTransaksi(Pesanan $pesanan, string $referensi): TransaksiBaru
    {
        return new TransaksiBaru('simulasi-'.Str::random(24), route('pembayaran.simulasi', $pesanan));
    }

    public function bacaNotifikasi(array $payload): Notifikasi
    {
        return $this->notifikasi->baca($payload);
    }

    public function skripJendela(): ?array
    {
        return null;
    }

    /**
     * Notifikasi seperti yang dikirim Midtrans untuk pesanan ini.
     *
     * @param  string  $status  transaction_status Midtrans: settlement, expire, cancel, deny, pending
     */
    public function notifikasi(Pesanan $pesanan, string $status, ?int $nominal = null): array
    {
        $orderId = $pesanan->pembayaran?->referensi ?? $pesanan->nomor;
        $kodeStatus = ['settlement' => '200', 'pending' => '201'][$status] ?? '202';
        $grossAmount = ($nominal ?? $pesanan->total).'.00';

        return [
            'transaction_time' => now()->format('Y-m-d H:i:s'),
            'transaction_status' => $status,
            'transaction_id' => 'simulasi-'.$orderId,
            'status_code' => $kodeStatus,
            'signature_key' => $this->notifikasi->tandaTangan($orderId, $kodeStatus, $grossAmount),
            'payment_type' => 'simulasi',
            'order_id' => $orderId,
            'gross_amount' => $grossAmount,
            'fraud_status' => 'accept',
            'currency' => 'IDR',
        ];
    }
}
