<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class DaftarPesanan extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.daftar-pesanan', [
            'pesanans' => Auth::user()->pesanans()->with('items')->paginate(10),
        ])
            ->extends('layouts.app')
            ->section('content');
    }
}
