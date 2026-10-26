<?php

namespace App\Livewire;

use App\Enums\StatusPesanan;
use App\Models\Pesanan;
use App\Models\User;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $order = Pesanan::status(StatusPesanan::Dibayar)->count();
        $users = User::count();
        $pengguna = User::all();

        return view('livewire.dashboard', [
            'dataorder' => $order,
            'user' => $users,
            'pengguna' => $pengguna,
        ])
            ->extends('layouts.adminapp')
            ->section('content');
    }
}
