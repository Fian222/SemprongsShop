<?php

namespace App\Pengiriman;

use RuntimeException;

// Ongkir tidak bisa dihitung; pesannya aman ditampilkan ke pembeli
class OngkirGagal extends RuntimeException
{
    // true bila yang perlu diperbaiki pembeli adalah alamatnya, bukan menunggu provider
    public bool $perluUbahAlamat = false;

    // provider tidak bisa dihubungi, menolak permintaan, atau menjawab dengan bentuk yang tidak dikenal
    public static function provider(string $detail): self
    {
        return new self('Ongkir belum bisa dihitung saat ini. Coba lagi sebentar lagi.', previous: new RuntimeException($detail));
    }

    public static function tanpaArea(): self
    {
        return tap(new self('Alamat ini belum punya area tujuan. Ubah alamat di buku alamat dan pilih areanya supaya ongkir bisa dihitung.'),
            fn (self $e) => $e->perluUbahAlamat = true);
    }

    public static function tanpaKurir(): self
    {
        return tap(new self('Belum ada kurir yang melayani area ini. Coba alamat lain.'), fn (self $e) => $e->perluUbahAlamat = true);
    }
}
