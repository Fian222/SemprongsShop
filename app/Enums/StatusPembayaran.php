<?php

namespace App\Enums;

use App\Pembayaran\KejadianPembayaran;

enum StatusPembayaran: string
{
    case Menunggu = 'menunggu';
    case Tertunda = 'tertunda';
    case Dibayar = 'dibayar';
    case Kedaluwarsa = 'kedaluwarsa';
    case Dibatalkan = 'dibatalkan';
    // dua keadaan berikut perlu ditangani admin di luar sistem (dicek atau dikembalikan dananya)
    case NominalTidakCocok = 'nominal_tidak_cocok';
    case DibayarTerlambat = 'dibayar_terlambat';

    public static function dari(KejadianPembayaran $kejadian): self
    {
        return match ($kejadian) {
            KejadianPembayaran::Dibayar => self::Dibayar,
            KejadianPembayaran::Kedaluwarsa => self::Kedaluwarsa,
            KejadianPembayaran::Dibatalkan => self::Dibatalkan,
            KejadianPembayaran::Tertunda => self::Tertunda,
        };
    }
}
