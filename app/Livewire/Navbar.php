<?php

namespace App\Livewire;

use App\Models\Keranjang;
use Livewire\Attributes\On;
use Livewire\Component;

class Navbar extends Component
{
    // rute halaman saat navbar pertama dirender; request() saat re-render Livewire adalah endpoint update
    public $rute;

    public function mount()
    {
        $this->rute = request()->route()?->getName();
    }

    // render ulang supaya jumlah di ikon dan ringkasan keranjang mengikuti isinya
    #[On('keranjang-diperbarui')]
    public function keranjangDiperbarui() {}

    public function render()
    {
        $keranjang = Keranjang::milikPengunjung();
        $keranjang?->load('items.varian.produk.fotoUtama');

        return view('livewire.navbar', [
            'keranjang' => $keranjang,
            'jumlahBarang' => $keranjang?->jumlahBarang() ?? 0,
        ]);
    }
}
