<?php

namespace App\Exceptions;

use App\Models\Voucher;
use App\Support\Format;
use DomainException;

// Voucher yang tidak bisa dipakai untuk belanjaan ini; pesannya siap ditampilkan ke pembeli
class VoucherDitolak extends DomainException
{
    public static function tidakDikenal(string $kode): self
    {
        return new self("Kode voucher {$kode} tidak dikenal.");
    }

    public static function nonaktif(Voucher $voucher): self
    {
        return new self("Voucher {$voucher->kode} sedang tidak berlaku.");
    }

    public static function belumMulai(Voucher $voucher): self
    {
        return new self("Voucher {$voucher->kode} baru berlaku mulai ".Format::tanggal($voucher->mulai).'.');
    }

    public static function berakhir(Voucher $voucher): self
    {
        return new self("Voucher {$voucher->kode} sudah berakhir pada ".Format::tanggal($voucher->berakhir).'.');
    }

    public static function kuotaHabis(Voucher $voucher): self
    {
        return new self("Kuota voucher {$voucher->kode} sudah habis.");
    }

    public static function diBawahMinimum(Voucher $voucher, int $subtotal): self
    {
        return new self("Voucher {$voucher->kode} berlaku untuk belanja minimal ".Format::rupiah($voucher->min_subtotal)
            .'. Tambah '.Format::rupiah($voucher->min_subtotal - $subtotal).' lagi untuk memakainya.');
    }
}
