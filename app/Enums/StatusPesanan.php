<?php

namespace App\Enums;

enum StatusPesanan: string
{
    case MenungguPembayaran = 'menunggu_pembayaran';
    case Dibayar = 'dibayar';
    case Diproses = 'diproses';
    case Dikirim = 'dikirim';
    case Selesai = 'selesai';
    case Kedaluwarsa = 'kedaluwarsa';
    case Dibatalkan = 'dibatalkan';

    // satu-satunya transisi yang sah (spec #10, mesin status pesanan)
    private const TRANSISI = [
        'menunggu_pembayaran' => ['dibayar', 'kedaluwarsa', 'dibatalkan'],
        'dibayar' => ['diproses'],
        'diproses' => ['dikirim'],
        'dikirim' => ['selesai'],
    ];

    public function bisaMenjadi(self $tujuan): bool
    {
        return in_array($tujuan->value, self::TRANSISI[$this->value] ?? [], true);
    }

    public function label(): string
    {
        return match ($this) {
            self::MenungguPembayaran => 'Menunggu pembayaran',
            self::Dibayar => 'Dibayar',
            self::Diproses => 'Diproses',
            self::Dikirim => 'Dikirim',
            self::Selesai => 'Selesai',
            self::Kedaluwarsa => 'Kedaluwarsa',
            self::Dibatalkan => 'Dibatalkan',
        };
    }

    // kelas badge di app.css
    public function badge(): string
    {
        return match ($this) {
            self::MenungguPembayaran => 'badge-warn',
            self::Dibayar, self::Diproses, self::Dikirim => 'badge-low',
            self::Selesai => 'badge-ok',
            self::Kedaluwarsa, self::Dibatalkan => 'badge-out',
        };
    }

    /**
     * Status sesudah pembayaran diterima.
     *
     * @return list<self>
     */
    public static function sudahDibayar(): array
    {
        return [self::Dibayar, self::Diproses, self::Dikirim, self::Selesai];
    }

    /**
     * Status yang memegang satu kuota Voucher: sudah dibayar, atau masih bisa dibayar.
     *
     * @return list<self>
     */
    public static function memegangKuota(): array
    {
        return [self::MenungguPembayaran, ...self::sudahDibayar()];
    }

    // stok yang dipesan dikembalikan saat pesanan berakhir tanpa dibayar
    public function mengembalikanStok(): bool
    {
        return in_array($this, [self::Kedaluwarsa, self::Dibatalkan], true);
    }

    // kolom waktu transisi di tabel pesanans
    public function kolomWaktu(): ?string
    {
        return $this === self::MenungguPembayaran ? null : $this->value.'_at';
    }
}
