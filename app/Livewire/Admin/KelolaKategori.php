<?php

namespace App\Livewire\Admin;

use App\Livewire\Forms\KategoriForm;
use App\Models\Kategori;
use Livewire\Component;

class KelolaKategori extends Component
{
    public KategoriForm $form;

    public function ubah($id)
    {
        $this->form->isi(Kategori::findOrFail($id));
        $this->resetValidation();
    }

    public function batal()
    {
        $this->form->reset();
        $this->resetValidation();
    }

    public function simpan()
    {
        $baru = ! $this->form->kategori;
        $kategori = $this->form->simpan();

        session()->flash('message', $baru ? "Kategori {$kategori->nama} ditambahkan." : "Kategori {$kategori->nama} disimpan.");
    }

    public function render()
    {
        return view('livewire.admin.kelola-kategori', [
            'kategoris' => Kategori::terurut()->withCount('produks')->get(),
        ])
            ->extends('layouts.adminapp')
            ->section('content');
    }
}
