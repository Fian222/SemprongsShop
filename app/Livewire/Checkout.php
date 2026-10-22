<?php

namespace App\Livewire;

use App\Models\barang;
use App\Models\User;
use App\Models\pesanan;
use App\Models\pesanandetail;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Checkout extends Component
{
    public $total_harga, $nohp, $alamat;

    public function mount()
    {
        if (!Auth::user()) {
            return redirect()->route('login');
        }

        $this->nohp = Auth::user()->notelp;
        $this->alamat = Auth::user()->alamat;

        $pesanan = pesanan::where('id_user', Auth::user()->id)->where('status', 0)->first();
        if (!empty($pesanan)) {
            $this->total_harga = $pesanan->total_harga;
        } else {
            return redirect()->route('home');
        }
    }

    public function checkout()
    {
        $this->validate([
            'nohp' => 'required',
            'alamat' => 'required'
        ]);

        // simpan nomor hp dan alamat ke user
        $user = User::where('id', Auth::user()->id)->first();
        $user->notelp = $this->nohp;
        $user->alamat = $this->alamat;
        $user->update();

        // update pesanan
        $pesanan = pesanan::where('id_user', Auth::user()->id)->where('status', 0)->first();
        $pesanan->status = 1;
        $pesanan->update();

        $this->dispatch('masukKeranjang');

        return redirect()->route('history');
        session()->flash('message', 'Berhasil Checkout');
    }

    public function render()
    {
        return view('livewire.checkout')
            ->extends('layouts.app')
            ->section('content');
    }
}
