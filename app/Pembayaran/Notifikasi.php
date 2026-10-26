<?php

namespace App\Pembayaran;

final readonly class Notifikasi
{
    public function __construct(
        // order_id di gateway; lihat Pembayaran::referensi
        public string $referensi,
        public string $transaksiId,
        public KejadianPembayaran $kejadian,
        public int $nominal,
        public ?string $metode,
        public array $payload,
    ) {}
}
