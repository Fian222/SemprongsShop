<?php

namespace App\Livewire;

use App\Models\pesanan;
use App\Models\User;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $order = pesanan::where('status', '=', 1)->count();
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
