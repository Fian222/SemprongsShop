<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\pesanan;
use App\Models\pesanandetail;
use Illuminate\Support\Facades\Auth;


class Pesanann extends Component
{
    protected $pesanan;
    protected $pesanan_details = [];

    public function destroy($id)
    {
        $pesanan_detail = pesanandetail::find($id);
        if (!empty($pesanan_detail)) {
            $pesanan = pesanan::where('id', $pesanan_detail->id_pesanan)->first();
            $jumlah_pesanan_detail = pesanandetail::Where('id_pesanan', $pesanan->id)->count();
            $pesanan_detail->delete();
            if ($jumlah_pesanan_detail == 1) {
                $pesanan->delete();
            } else {
                $pesanan->total_harga = $pesanan->total_harga - $pesanan_detail->total_harga;
                $pesanan->update();
            }
            $this->dispatch('masukKeranjang');
            session()->flash('message', 'Pesanan dihapus');
        }
    }

    public function mount()
    {
        if (!Auth::user()) {
            return redirect()->route('login');
        }
    }

    public function render()
    {
        if (Auth::user()) {
            $this->pesanan = pesanan::where('id_user', Auth::user()->id)->where('status', 0)->first();
            if ($this->pesanan) {
                $this->pesanan_details = pesanandetail::where('id_pesanan', $this->pesanan->id)->get();
            }
        }

        return view('livewire.pesanann', [
            'pesanan' => $this->pesanan,
            'pesanan_details' => $this->pesanan_details,
        ])
            ->extends('layouts.app')
            ->section('content');
    }
}
