<?php

namespace App\Livewire;

use App\Models\barang;
use Livewire\Component;

class Home extends Component
{
    public function render()
    {
        return view('livewire.home', [
            'barangs' => barang::orderBy('id')->take(8)->get(),
            'jumlahProduk' => barang::count(),
            'hargaMulai' => barang::min('harga_barang'),
        ])
        ->extends('layouts.app')
        ->section('content');
    }
}
