<?php

use App\Enums\StatusPesanan;
use App\Exceptions\TransisiTidakSah;
use App\Models\Pesanan;
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

Schedule::command('pesanan:kedaluwarsakan')->everyFiveMinutes()->withoutOverlapping();
