<?php

namespace App\Livewire\Admin;

use App\Models\Produk;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class DaftarProduk extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public $cari = '';

    public function updatingCari()
    {
        $this->resetPage();
    }

    public function render()
    {
        $produks = Produk::untukKartu()->with('kategori')->withCount('varianAktif')
            ->when($this->cari, fn ($q) => $q->where('nama', 'like', '%'.$this->cari.'%'))
            ->latest('id')
            ->paginate(12);

        return view('livewire.admin.daftar-produk', [
            'produks' => $produks,
        ])
            ->extends('layouts.adminapp')
            ->section('content');
    }
}
