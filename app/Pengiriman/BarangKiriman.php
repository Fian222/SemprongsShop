<?php

namespace App\Pengiriman;

final readonly class BarangKiriman
{
    // berat dalam gram per satuan, nilai dalam rupiah per satuan
    public function __construct(public string $nama, public int $nilai, public int $berat, public int $jumlah) {}

    /**
     * @param  list<self>  $barang
     */
    public static function totalBerat(array $barang): int
    {
        return array_sum(array_map(fn (self $b) => $b->berat * $b->jumlah, $barang));
    }
}
