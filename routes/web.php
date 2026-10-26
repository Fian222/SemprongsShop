<?php

use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\NotifikasiPembayaranController;
use App\Http\Controllers\SimulasiPembayaranController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();

Route::get('/', \App\Livewire\Home::class)->name('home');
Route::get('/produk', \App\Livewire\Katalog::class)->name('produk');
Route::get('/produk/{slug}', \App\Livewire\ProdukDetail::class)->name('produk.detail');
Route::get('/tas-belanja', \App\Livewire\KeranjangBelanja::class)->name('keranjang');
Route::get('/checkout', \App\Livewire\Checkout::class)->name('checkout');

Route::middleware('auth')->group(function () {
    Route::get('/pesanan', \App\Livewire\DaftarPesanan::class)->name('pesanan.index');
    Route::get('/pesanan/{pesanan}', \App\Livewire\DetailPesanan::class)->name('pesanan.detail');
    Route::get('/akun/alamat', \App\Livewire\BukuAlamat::class)->name('alamat');

    // jendela pembayaran PEMBAYARAN_DRIVER=simulasi, pengganti Snap saat pengembangan
    Route::get('/pembayaran/simulasi/{pesanan}', [SimulasiPembayaranController::class, 'tampil'])->name('pembayaran.simulasi');
    Route::post('/pembayaran/simulasi/{pesanan}', [SimulasiPembayaranController::class, 'kirim']);
});

// webhook payment gateway; dikecualikan dari CSRF di bootstrap/app.php
Route::post('/pembayaran/notifikasi', NotifikasiPembayaranController::class)->name('pembayaran.notifikasi');

Route::prefix('admin')->group(function () {
    Route::get('/', [AdminLoginController::class, 'loginForm']);
    Route::get('/login', [AdminLoginController::class, 'loginForm'])->name('admin.login');
    Route::post('/login', [AdminLoginController::class, 'login'])->name('admin.login');
    Route::get('/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');
    Route::middleware('adminMiddle')->group(function () {
        Route::get('/dashboard', \App\Livewire\Dashboard::class)->name('admin.dashboard');
        Route::get('/pesananmasuk', \App\Livewire\Pesananmasuk::class)->name('admin.pesanan');
        Route::get('/riwayatpesanan', \App\Livewire\Riwayatpesanan::class)->name('admin.riwayat');
        Route::get('/kategori', \App\Livewire\Admin\KelolaKategori::class)->name('admin.kategori');
        Route::get('/produk', \App\Livewire\Admin\DaftarProduk::class)->name('admin.produk');
        Route::get('/produk/baru', \App\Livewire\Admin\FormProduk::class)->name('admin.produk.baru');
        Route::get('/produk/{produk}/ubah', \App\Livewire\Admin\FormProduk::class)->name('admin.produk.ubah');
    });
});
