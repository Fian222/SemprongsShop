<?php

namespace App\Livewire;

use App\Exceptions\KeranjangDitolak;
use App\Models\Keranjang;
use App\Models\Produk;
use App\Models\Varian;
use Livewire\Component;

class ProdukDetail extends Component
{
    public Produk $produk;

    public $varianId;

    public $jumlah = 1;

    // varian sebelum diganti, untuk dikembalikan kalau pilihan baru tidak sah
    protected $varianSebelumnya;

    public function mount($slug)
    {
        $this->produk = Produk::tampil()->where('slug', $slug)->with(['kategori', 'fotos', 'varianAktif'])->firstOrFail();

        $this->varianId = $this->varianAwal()->id;
    }

    public function updatingVarianId()
    {
        $this->varianSebelumnya = $this->varianId;
    }

    public function updatedVarianId()
    {
        $varian = $this->produk->varianAktif->find((int) $this->varianId);

        $this->varianId = $varian && ! $varian->habis() ? $varian->id : $this->varianSebelumnya;
        $this->resetValidation();
    }

    // Varian terpilih. Kalau varian itu dinonaktifkan selagi halaman terbuka, kembali ke varian awal.
    public function varian(): Varian
    {
        $varian = $this->produk->varianAktif->find($this->varianId) ?? $this->varianAwal();
        $this->varianId = $varian->id;

        return $varian;
    }

    // varian termurah yang masih ada stoknya; kalau semua habis, yang termurah
    protected function varianAwal(): Varian
    {
        $varians = $this->produk->varianAktif;
        abort_if($varians->isEmpty(), 404);

        return $varians->first(fn (Varian $v) => ! $v->habis()) ?? $varians->first();
    }

    public function tambahKeKeranjang()
    {
        $this->validate(['jumlah' => ['required', 'integer', 'min:1']]);

        // jangan diam-diam mengganti varian yang dilihat pembeli
        if (! $this->produk->varianAktif->find($this->varianId)) {
            $this->varian();

            return $this->addError('jumlah', 'Kemasan yang kamu pilih sudah tidak dijual. Pilih kemasan lain.');
        }

        try {
            Keranjang::milikPengunjungAtauBaru()->tambah($this->varian()->fresh(), (int) $this->jumlah);
        } catch (KeranjangDitolak $e) {
            return $this->addError('jumlah', $e->getMessage());
        }

        $this->jumlah = 1;
        $this->dispatch('keranjang-diperbarui', buka: true);
        session()->flash('message', 'Masuk ke tas belanja.');
    }

    public function render()
    {
        return view('livewire.produk-detail', [
            'varian' => $this->varian(),
        ])
            ->extends('layouts.app')
            ->section('content');
    }
}
