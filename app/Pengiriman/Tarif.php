<?php

namespace App\Pengiriman;

final readonly class Tarif
{
    public function __construct(
        public string $kurir,
        public string $namaKurir,
        public string $layanan,
        public string $namaLayanan,
        public int $ongkir,
        public string $estimasi,
        // total berat (gram) yang dipakai menghitung tarif ini
        public int $berat,
    ) {}

    // mis. "JNE Reguler"
    public function label(): string
    {
        return $this->namaKurir.' '.$this->namaLayanan;
    }

    // pengenal pilihan di checkout, mis. "jne:reg"
    public function kunci(): string
    {
        return $this->kurir.':'.$this->layanan;
    }

    /**
     * @param  list<self>  $tarif
     */
    public static function cari(array $tarif, ?string $kunci): ?self
    {
        return collect($tarif)->first(fn (self $t) => $t->kunci() === $kunci);
    }
}
