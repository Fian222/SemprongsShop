<?php

namespace App\Livewire;

use App\Models\pesanan;
use Livewire\Component;

class Pesananmasuk extends Component
{
    protected $pesanan;

    public function konfirmasi($id)
    {
        $pesanan = pesanan::find($id);
        $pesanan->status = 2;
        $pesanan->update();

        session()->flash('message', 'Berhasil dikonfirmasi');
    }

    public function render()
    {
        $this->pesanan = pesanan::where('status', '=', 1)->get();
        return view('livewire.pesananmasuk', [
            'pesanan' => $this->pesanan,
        ])
            ->extends('layouts.adminapp')
            ->section('content');
    }
}
