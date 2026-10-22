<?php

namespace App\Livewire;

use App\Models\pesanan;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class History extends Component
{
    public $pesanan;

    public function mount()
    {
        if (!Auth::user()) {
            return redirect()->route('login');
        }
    }

    public function render()
    {
        if (Auth::user()) {
            $this->pesanan = pesanan::where('id_user', Auth::user()->id)->where('status', '!=', 0)->get();
        }

        return view('livewire.history')
            ->extends('layouts.app')
            ->section('content');
    }
}
