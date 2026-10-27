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

    // tautan stok menipis di dashboard membuka langsung form ubah varian (?varian=id)
    public function mount()
    {
        $varian = $this->produk->varians()->find(request()->integer('varian'));
        if ($varian) {
            $this->form->isi($varian);
        }
    }

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
