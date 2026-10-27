<?php

use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\EksporPesananController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\NotifikasiPembayaranController;
use App\Http\Controllers\SimulasiPembayaranController;
use App\Livewire\Admin\DaftarPesanan as AdminDaftarPesanan;
use App\Livewire\Admin\DaftarProduk;
use App\Livewire\Admin\DetailPesanan as AdminDetailPesanan;
use App\Livewire\Admin\FormProduk;
use App\Livewire\Admin\KelolaKategori;
use App\Livewire\Admin\KelolaVoucher;
use App\Livewire\BukuAlamat;
use App\Livewire\Checkout;
use App\Livewire\DaftarPesanan;
use App\Livewire\Dashboard;
use App\Livewire\DetailPesanan;
use App\Livewire\Home;
use App\Livewire\Katalog;
use App\Livewire\KeranjangBelanja;
use App\Livewire\ProdukDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();

Route::get('/', Home::class)->name('home');
Route::get('/produk', Katalog::class)->name('produk');
Route::get('/produk/{slug}', ProdukDetail::class)->name('produk.detail');
Route::get('/tas-belanja', KeranjangBelanja::class)->name('keranjang');
Route::get('/checkout', Checkout::class)->name('checkout');

Route::middleware('auth')->group(function () {
    Route::get('/pesanan', DaftarPesanan::class)->name('pesanan.index');
    Route::get('/pesanan/{pesanan}', DetailPesanan::class)->name('pesanan.detail');
    Route::get('/pesanan/{pesanan}/invoice', InvoiceController::class)->name('pesanan.invoice');
    Route::get('/akun/alamat', BukuAlamat::class)->name('alamat');

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
        Route::get('/dashboard', Dashboard::class)->name('admin.dashboard');
        Route::get('/pesanan', AdminDaftarPesanan::class)->name('admin.pesanan');
        Route::get('/pesanan/ekspor', EksporPesananController::class)->name('admin.pesanan.ekspor');
        Route::get('/pesanan/{pesanan}', AdminDetailPesanan::class)->name('admin.pesanan.detail');
        Route::get('/pesanan/{pesanan}/invoice', InvoiceController::class)->name('admin.pesanan.invoice');
        Route::get('/kategori', KelolaKategori::class)->name('admin.kategori');
        Route::get('/voucher', KelolaVoucher::class)->name('admin.voucher');
        Route::get('/produk', DaftarProduk::class)->name('admin.produk');
        Route::get('/produk/baru', FormProduk::class)->name('admin.produk.baru');
        Route::get('/produk/{produk}/ubah', FormProduk::class)->name('admin.produk.ubah');
    });
});
