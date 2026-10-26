<?php

namespace App\Livewire;

use App\Models\Pesanan;
use Livewire\Component;
use Livewire\WithPagination;

// Interim sampai halaman pesanan admin #17: semua pesanan, terbaru di atas
class Riwayatpesanan extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.riwayatpesanan', [
            'pesanan' => Pesanan::with('user', 'items')->latest('id')->paginate(20),
        ])
            ->extends('layouts.adminapp')
            ->section('content');
    }
}
