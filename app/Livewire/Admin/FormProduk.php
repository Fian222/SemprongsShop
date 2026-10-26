<?php

namespace App\Livewire\Admin;

use App\Livewire\Forms\ProdukForm;
use App\Models\Kategori;
use App\Models\Produk;
use Livewire\Component;

// Tambah produk (tanpa $produk) dan ubah produk beserta varian dan galerinya
class FormProduk extends Component
{
    public ProdukForm $form;

    public function mount(?Produk $produk = null)
    {
        if ($produk?->exists) {
            $this->form->isi($produk);
        }
    }

    public function simpan()
    {
        $baru = ! $this->form->produk;
        $produk = $this->form->simpan();

        if ($baru) {
            session()->flash('message', "Produk {$produk->nama} dibuat. Tambahkan varian dan foto supaya bisa tampil di toko.");

            return $this->redirectRoute('admin.produk.ubah', $produk, navigate: true);
        }

        session()->flash('message', "Produk {$produk->nama} disimpan.");
    }

    public function render()
    {
        return view('livewire.admin.form-produk', [
            'kategoris' => Kategori::terurut()->get(),
        ])
            ->extends('layouts.adminapp')
            ->section('content');
    }
}
