<?php

namespace App\Livewire;

use App\Models\barang;
use Livewire\Component;

class Home extends Component
{
    public function render()
    {
        return view('livewire.home', [
            'barangs' => barang::take(3)->get(),
        ])
        ->extends('layouts.app')
        ->section('content');
    }
}
