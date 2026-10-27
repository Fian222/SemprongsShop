<?php

namespace Tests\Concerns;

use App\Models\Alamat;
use App\Models\Keranjang;
use App\Models\Pesanan;
use App\Models\User;
use App\Pengiriman\ShippingProvider;
use App\Pengiriman\Tarif;

// Membuat pesanan lewat jalur yang sama dengan checkout, dengan ongkir dari ShippingProvider simulasi
trait MembuatPesanan
{
    protected function alamatUntuk(User $user, array $data = []): Alamat
    {
        return $user->alamats()->create(array_merge([
            'label' => 'Rumah',
            'penerima' => 'Budi Santoso',
            'no_hp' => '081234567890',
            'alamat' => 'Jl. Merdeka 1, Bandung',
            'kode_pos' => '40115',
            'area_id' => 'SIMULASI-40115',
            'area_nama' => 'Bandung Wetan, Bandung, Jawa Barat. 40115',
            'utama' => ! $user->alamats()->exists(),
        ], $data));
    }

    // kurir pertama yang ditawarkan, kecuali $kunci (mis. 'sicepat:reg') dipilih
    protected function pesananDari(Keranjang $keranjang, Alamat $alamat, ?string $kunci = null, ?string $kodeVoucher = null): Pesanan
    {
        $keranjang->load('items.varian.produk');
        $tarif = app(ShippingProvider::class)->tarif($alamat->area_id, $keranjang->barangKiriman());
        $tarif = $kunci ? Tarif::cari($tarif, $kunci) : $tarif[0];

        return Pesanan::buatDariKeranjang($keranjang, $alamat, $tarif, $kodeVoucher);
    }
}
