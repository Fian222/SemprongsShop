<?php

use App\Enums\StatusPesanan;
use App\Exceptions\TransisiTidakSah;
use App\Models\Pesanan;
use App\Pengiriman\PelacakanGagal;
use App\Pengiriman\ShippingProvider;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// jaring pengaman bila webhook expire dari payment gateway terlewat
Artisan::command('pesanan:kedaluwarsakan', function () {
    $jumlah = 0;

    Pesanan::status(StatusPesanan::MenungguPembayaran)
        ->where('batas_bayar', '<', now())
        // lazyById, bukan each(): baris yang sudah diubah keluar dari filter dan menggeser halaman offset
        ->lazyById()
        ->each(function (Pesanan $pesanan) use (&$jumlah) {
            try {
                $pesanan->ubahStatus(StatusPesanan::Kedaluwarsa);
                $jumlah++;
            } catch (TransisiTidakSah) {
                // sudah dibayar atau dibatalkan oleh notifikasi yang datang bersamaan
            }
        });

    $this->info("{$jumlah} pesanan kedaluwarsa.");
})->purpose('Menandai pesanan yang lewat batas bayar sebagai kedaluwarsa dan mengembalikan stoknya');

// status lacak "delivered" menyelesaikan pesanan meskipun tidak ada yang membuka halaman pesanannya
Artisan::command('pengiriman:lacak', function (ShippingProvider $provider) {
    $selesai = 0;

    Pesanan::status(StatusPesanan::Dikirim)->with('pengiriman')->lazyById()->each(function (Pesanan $pesanan) use ($provider, &$selesai) {
        try {
            $pesanan->lacakPaket($provider);
        } catch (PelacakanGagal $e) {
            $this->warn("{$pesanan->nomor}: {$e->getPrevious()?->getMessage()}");

            return;
        }

        $selesai += $pesanan->status === StatusPesanan::Selesai ? 1 : 0;
    });

    $this->info("{$selesai} pesanan selesai menurut pelacakan.");
})->purpose('Melacak paket pesanan yang sedang dikirim dan menyelesaikan yang sudah diterima');

Schedule::command('pesanan:kedaluwarsakan')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('pengiriman:lacak')->hourly()->withoutOverlapping();
