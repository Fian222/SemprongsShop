<?php

namespace App\Livewire;

use App\Models\pesanan;
use Livewire\Component;

class Riwayatpesanan extends Component
{
    protected $pesanan;
    public function render()
    {
        $this->pesanan = pesanan::where('status', '=', 2)->get();
        return view('livewire.riwayatpesanan', [
            'pesanan' => $this->pesanan,
        ])
            ->extends('layouts.adminapp')
            ->section('content');
    }
}
