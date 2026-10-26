<?php

namespace App\Livewire;

use App\Enums\StatusPesanan;
use App\Models\Pesanan;
use Livewire\Component;

// Interim sampai halaman pesanan admin #17: pesanan yang sudah dibayar dan perlu disiapkan
class Pesananmasuk extends Component
{
    public function render()
    {
        return view('livewire.pesananmasuk', [
            'pesanan' => Pesanan::status(StatusPesanan::Dibayar)->with('user', 'items')->oldest('dibayar_at')->get(),
        ])
            ->extends('layouts.adminapp')
            ->section('content');
    }
}
