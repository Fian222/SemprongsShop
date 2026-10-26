<?php

use App\Http\Controllers\AdminLoginController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();

Route::get('/', \App\Livewire\Home::class)->name('home');
Route::get('/produk', \App\Livewire\Katalog::class)->name('produk');
Route::get('/produk/{slug}', \App\Livewire\ProdukDetail::class)->name('produk.detail');
Route::get('/pesanann', \App\Livewire\Pesanann::class)->name('pesanann');
Route::get('/checkout', \App\Livewire\Checkout::class)->name('checkout');
Route::get('/history', \App\Livewire\History::class)->name('history');

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
