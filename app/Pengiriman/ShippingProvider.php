<?php

namespace App\Pengiriman;

/**
 * Port pengiriman (spec #10). Adapter: BiteshipShippingProvider dan SimulasiShippingProvider (pengembangan dan test).
 * Area asal paket diatur di adapter dari config/pengiriman.php.
 */
interface ShippingProvider
{
    /**
     * Mencari area tujuan dari teks, mis. nama kecamatan atau kota.
     *
     * @return list<Area>
     *
     * @throws OngkirGagal
     */
    public function cariArea(string $teks): array;

    /**
     * Pilihan kurir dan layanan untuk mengirim barang ke area tujuan.
     *
     * @param  list<BarangKiriman>  $barang
     * @return list<Tarif>
     *
     * @throws OngkirGagal
     */
    public function tarif(string $areaTujuan, array $barang): array;
}
