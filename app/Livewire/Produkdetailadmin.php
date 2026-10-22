<?php

namespace App\Livewire;

use App\Models\barang;
use Livewire\Component;

class Produkdetailadmin extends Component
{

    protected $listeners = ['refreshComponent' => '$refresh'];
    public $product, $jumlah_tambah;
    public $ubah_harga;

    public function mount($id)
    {

        $barangs = barang::find($id);

        if ($barangs) {
            $this->product = $barangs;
        }
    }

    public function edit($id)
    {
        $product = barang::find($id);
        if ($this->ubah_harga == 0) {
            $product->harga_barang = $product->harga_barang;
        } else {
            $product->harga_barang = $this->ubah_harga;
        }
        $product->stok_barang = $product->stok_barang + $this->jumlah_tambah;
        $product->update();
        $this->dispatch('refreshComponent');
    }

    public function render()
    {
        return view('livewire.produkdetailadmin', [
            'barang' => $this->product,
        ])
            ->extends('layouts.adminapp')
            ->section('content');
    }
}
