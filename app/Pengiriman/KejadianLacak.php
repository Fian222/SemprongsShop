<?php

namespace App\Pengiriman;

use Carbon\CarbonImmutable;

// Satu catatan perjalanan paket dari kurir, mis. "Paket dijemput kurir"
final readonly class KejadianLacak
{
    public function __construct(public CarbonImmutable $waktu, public string $keterangan) {}
}
