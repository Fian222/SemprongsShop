<?php

namespace App\Pembayaran;

/**
 * Membaca notifikasi HTTP berformat Midtrans. Dipakai MidtransGateway dengan server key sungguhan dan
 * SimulasiGateway dengan kunci lokal, supaya simulasi melewati verifikasi dan pemetaan status yang sama.
 */
final class NotifikasiMidtrans
{
    public function __construct(private string $serverKey) {}

    public function baca(array $payload): Notifikasi
    {
        // tanpa server key, siapa pun bisa membuat signature yang "cocok"
        if ($this->serverKey === '') {
            throw new NotifikasiTidakSah('Server key belum diisi.');
        }

        foreach (['order_id', 'status_code', 'gross_amount', 'signature_key', 'transaction_status', 'transaction_id'] as $kolom) {
            if (! is_string($payload[$kolom] ?? null)) {
                throw new NotifikasiTidakSah("Kolom {$kolom} tidak ada.");
            }
        }

        if (! hash_equals($this->tandaTangan($payload['order_id'], $payload['status_code'], $payload['gross_amount']), $payload['signature_key'])) {
            throw new NotifikasiTidakSah('Signature tidak cocok.');
        }

        return new Notifikasi(
            referensi: $payload['order_id'],
            transaksiId: $payload['transaction_id'],
            kejadian: $this->kejadian($payload['transaction_status'], $payload['fraud_status'] ?? null),
            nominal: (int) round((float) $payload['gross_amount']),
            metode: $payload['payment_type'] ?? null,
            payload: $payload,
        );
    }

    // signature_key = SHA512(order_id + status_code + gross_amount + server key)
    public function tandaTangan(string $orderId, string $statusCode, string $grossAmount): string
    {
        return hash('sha512', $orderId.$statusCode.$grossAmount.$this->serverKey);
    }

    private function kejadian(string $status, ?string $fraud): KejadianPembayaran
    {
        return match ($status) {
            'settlement' => KejadianPembayaran::Dibayar,
            // pembayaran kartu: capture baru dianggap dibayar bila lolos pemeriksaan fraud
            'capture' => $fraud === 'accept' || $fraud === null ? KejadianPembayaran::Dibayar : KejadianPembayaran::Tertunda,
            'expire' => KejadianPembayaran::Kedaluwarsa,
            'cancel', 'deny' => KejadianPembayaran::Dibatalkan,
            default => KejadianPembayaran::Tertunda,
        };
    }
}
