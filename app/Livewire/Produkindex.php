<?php

namespace App\Livewire;

use App\Models\barang;
use Livewire\Component;
use Livewire\WithPagination;

class Produkindex extends Component
{
    use WithPagination;

    public $search;

    protected $updateQueryString = ['search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        if ($this->search) {
            $barangs = barang::where('nama_barang', 'like', '%' . $this->search . '%')->paginate(6);
        } else {
            $barangs = barang::paginate(6);
        }
        return view('livewire.produkindex', [
            'barangs' => $barangs
        ])
            ->extends('layouts.app')
            ->section('content');
    }
}
