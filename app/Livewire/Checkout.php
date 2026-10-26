<?php

namespace App\Livewire;

use App\Exceptions\KeranjangDitolak;
use App\Livewire\Forms\AlamatForm;
use App\Models\Keranjang;
use App\Models\Pesanan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Checkout extends Component
{
    public $alamatId;

    public AlamatForm $form;

    // formulir alamat baru terbuka di halaman checkout
    public $tambahAlamat = false;

    public function mount()
    {
        if (! Auth::user()) {
            return redirect()->guest(route('login'));
        }

        if (! Keranjang::milikPengunjung()?->items()->exists()) {
            return redirect()->route('keranjang');
        }

        $this->alamatId = Auth::user()->alamats()->value('id');
        $this->tambahAlamat = ! $this->alamatId;
    }

    public function simpanAlamat()
    {
        $this->alamatId = $this->form->simpan(Auth::user())->id;
        $this->tambahAlamat = false;
    }

    public function bayar()
    {
        $this->validate([
            'alamatId' => ['required', Rule::exists('alamats', 'id')->where('user_id', Auth::id())],
        ], [
            'alamatId.required' => 'Pilih atau tambahkan alamat pengiriman dulu.',
        ]);

        $keranjang = Keranjang::milikPengunjung();
        if (! $keranjang?->items()->exists()) {
            return redirect()->route('keranjang');
        }

        try {
            $pesanan = Pesanan::buatDariKeranjang($keranjang, Auth::user()->alamats()->find($this->alamatId));
        } catch (KeranjangDitolak $e) {
            return $this->addError('keranjang', $e->getMessage());
        }

        $this->dispatch('keranjang-diperbarui');

        // halaman detail langsung membuka jendela pembayaran
        return redirect()->to(route('pesanan.detail', $pesanan).'?bayar=1');
    }

    public function render()
    {
        $keranjang = Keranjang::milikPengunjung();
        $keranjang?->load('items.varian.produk.fotoUtama');

        return view('livewire.checkout', [
            'keranjang' => $keranjang,
            'alamats' => Auth::user()->alamats,
        ])
            ->extends('layouts.app')
            ->section('content');
    }
}
