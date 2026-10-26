<?php

namespace App\Livewire;

use App\Exceptions\KeranjangDitolak;
use App\Models\Keranjang;
use Livewire\Component;

// Halaman "Tas belanja": isi keranjang pengunjung, tamu maupun pelanggan
class KeranjangBelanja extends Component
{
    public function ubahJumlah($itemId, $jumlah)
    {
        $this->resetErrorBag();

        $jumlah = filter_var($jumlah, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        if ($jumlah === false) {
            return $this->addError("jumlah.$itemId", 'Jumlah harus berupa angka. Isi 0 untuk menghapus.');
        }

        try {
            Keranjang::milikPengunjung()?->ubahJumlah((int) $itemId, $jumlah);
        } catch (KeranjangDitolak $e) {
            $this->addError("jumlah.$itemId", $e->getMessage());
        }

        $this->dispatch('keranjang-diperbarui');
    }

    public function hapus($itemId)
    {
        Keranjang::milikPengunjung()?->hapus((int) $itemId);

        $this->dispatch('keranjang-diperbarui');
    }

    public function render()
    {
        $keranjang = Keranjang::milikPengunjung();
        $keranjang?->load('items.varian.produk.fotoUtama');

        return view('livewire.keranjang-belanja', [
            'keranjang' => $keranjang,
            'items' => $keranjang?->items ?? collect(),
        ])
            ->extends('layouts.app')
            ->section('content');
    }
}
