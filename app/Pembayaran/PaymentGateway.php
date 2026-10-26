<?php

namespace App\Pembayaran;

use App\Models\Pesanan;

/**
 * Port pembayaran (spec #10). Adapter: MidtransGateway (Snap) dan SimulasiGateway (pengembangan dan test).
 */
interface PaymentGateway
{
    // nama provider yang disimpan di tabel pembayarans
    public function nama(): string;

    /**
     * Membuat transaksi pembayaran untuk pesanan; token dipakai membuka jendela pembayaran.
     *
     * @param  string  $referensi  order_id unik di gateway untuk percobaan ini
     */
    public function buatTransaksi(Pesanan $pesanan, string $referensi): TransaksiBaru;

    /**
     * Memverifikasi notifikasi dari provider dan menerjemahkannya menjadi kejadian domain.
     *
     * @throws NotifikasiTidakSah
     */
    public function bacaNotifikasi(array $payload): Notifikasi;

    /**
     * Atribut tag <script> yang membuka jendela pembayaran di atas halaman, atau null bila jendela
     * pembayaran dibuka dengan berpindah ke redirect_url transaksi.
     */
    public function skripJendela(): ?array;
}
