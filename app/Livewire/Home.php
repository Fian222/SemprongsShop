<?php

namespace App\Livewire;

use App\Models\Produk;
use App\Models\Varian;
use Livewire\Component;

class Home extends Component
{
    public function render()
    {
        return view('livewire.home', [
            // produk unggulan lebih dulu, sisanya yang terbaru
            'produks' => Produk::tampil()->untukKartu()->orderByDesc('unggulan')->latest('id')->take(8)->get(),
            'jumlahProduk' => Produk::tampil()->count(),
            'hargaMulai' => Varian::where('aktif', true)->whereHas('produk', fn ($q) => $q->tampil())->min('harga'),
        ])
        ->extends('layouts.app')
        ->section('content');
    }
}
