<?php

namespace App\Livewire\Admin;

use App\Livewire\Forms\VarianForm;
use App\Models\Produk;
use Livewire\Component;

// Varian kemasan satu produk, ditanam di halaman ubah produk
class VarianProduk extends Component
{
    public Produk $produk;

    public VarianForm $form;

    public function ubah($id)
    {
        $this->form->isi($this->produk->varians()->findOrFail($id));
        $this->resetValidation();
    }

    public function batal()
    {
        $this->form->reset();
        $this->resetValidation();
    }

    public function simpan()
    {
        $varian = $this->form->simpan($this->produk);

        session()->flash('message', "Varian {$varian->nama} disimpan.");
    }

    public function render()
    {
        return view('livewire.admin.varian-produk', [
            'varians' => $this->produk->varians()->get(),
        ]);
    }
}
