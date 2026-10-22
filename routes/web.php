<?php

use App\Http\Controllers\AdminHomeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/



Auth::routes();

Route::get('/', \App\Livewire\Home::class)->name('home');
Route::get('/produk', \App\Livewire\Produkindex::class)->name('produk');
Route::get('/produkdetail{id}', \App\Livewire\ProdukDetail::class)->name('produk.detail');
Route::get('/pesanann', \App\Livewire\Pesanann::class)->name('pesanann');
Route::get('/checkout', \App\Livewire\Checkout::class)->name('checkout');
Route::get('/history', \App\Livewire\History::class)->name('history');

Route::prefix('admin')->group(function () {
    Route::get('/', [AdminLoginController::class, 'loginForm']);
    Route::get('/login', [AdminLoginController::class, 'loginForm'])->name('admin.login');
    Route::post('/login', [AdminLoginController::class, 'login'])->name('admin.login');
    Route::get('/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');
    Route::get('/dashboard', \App\Livewire\Dashboard::class)->name('admin.dashboard');
    Route::get('/pesananmasuk', \App\Livewire\Pesananmasuk::class)->name('admin.pesanan');
    Route::get('/produkadmin', \App\Livewire\Produkadmin::class)->name('admin.produk');
    Route::get('/riwayatpesanan', \App\Livewire\Riwayatpesanan::class)->name('admin.riwayat');
    Route::get('/produkdetail{id}', \App\Livewire\Produkdetailadmin::class)->name('admin.detail');
    Route::get('/tambahbarang', \App\Livewire\Tambahbarang::class)->name('admin.tambah');
});
