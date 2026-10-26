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

    public function label(): string
    {
        return match ($this) {
            self::Menunggu => 'Menunggu',
            self::Tertunda => 'Tertunda',
            self::Dibayar => 'Dibayar',
            self::Kedaluwarsa => 'Kedaluwarsa',
            self::Dibatalkan => 'Dibatalkan',
            self::NominalTidakCocok => 'Nominal tidak cocok',
            self::DibayarTerlambat => 'Dibayar terlambat',
        };
    }

    public function perluDicek(): bool
    {
        return $this->tindakan() !== null;
    }

    // kelas badge di app.css
    public function badge(): string
    {
        return match ($this) {
            self::Dibayar => 'badge-ok',
            self::NominalTidakCocok, self::DibayarTerlambat => 'badge-bad',
            default => 'badge-low',
        };
    }

    // apa yang perlu dilakukan admin; null bila tidak ada
    public function tindakan(): ?string
    {
        return match ($this) {
            self::DibayarTerlambat => 'Pembeli membayar sesudah pesanan berakhir. Cek di dashboard Midtrans dan kembalikan dananya bila perlu.',
            self::NominalTidakCocok => 'Nominal yang dibayar berbeda dengan total pesanan. Cek di dashboard Midtrans sebelum memproses.',
            default => null,
        };
    }

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
