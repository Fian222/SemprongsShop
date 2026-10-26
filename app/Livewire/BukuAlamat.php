<?php

namespace App\Livewire;

use App\Livewire\Forms\AlamatForm;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class BukuAlamat extends Component
{
    public AlamatForm $form;

    // formulir terbuka untuk alamat baru atau alamat yang diubah
    public $terbuka = false;

    public function tambah()
    {
        $this->form->reset();
        $this->resetValidation();
        $this->terbuka = true;
    }

    public function ubah($id)
    {
        $this->form->isi(Auth::user()->alamats()->findOrFail($id));
        $this->resetValidation();
        $this->terbuka = true;
    }

    public function batal()
    {
        $this->form->reset();
        $this->terbuka = false;
    }

    public function simpan()
    {
        $alamat = $this->form->simpan(Auth::user());
        $this->terbuka = false;

        session()->flash('message', "Alamat {$alamat->label} disimpan.");
    }

    public function jadikanUtama($id)
    {
        Auth::user()->jadikanAlamatUtama(Auth::user()->alamats()->findOrFail($id));
    }

    public function hapus($id)
    {
        $alamat = Auth::user()->alamats()->findOrFail($id);
        Auth::user()->hapusAlamat($alamat);

        session()->flash('message', "Alamat {$alamat->label} dihapus.");
    }

    public function render()
    {
        return view('livewire.buku-alamat', [
            'alamats' => Auth::user()->alamats()->get(),
        ])
            ->extends('layouts.app')
            ->section('content');
    }
}
