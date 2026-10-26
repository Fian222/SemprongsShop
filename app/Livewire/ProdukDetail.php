<?php

namespace App\Livewire;

use App\Models\pesanan;
use App\Models\pesanandetail;
use App\Models\Produk;
use App\Models\Varian;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProdukDetail extends Component
{
    public Produk $produk;

    public $varianId;

    public $jumlah_pesanan = 1;

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

    // Masih memakai alur pesanan lama (status 0 = tas belanja) sampai keranjang #14 menggantikannya
    public function masukkankeranjang()
    {
        // jangan diam-diam mengganti varian yang dilihat pembeli
        if (! $this->produk->varianAktif->find($this->varianId)) {
            $this->varian();

            return $this->addError('jumlah_pesanan', 'Kemasan yang kamu pilih sudah tidak dijual. Pilih kemasan lain.');
        }

        $varian = $this->varian()->fresh();

        $this->validate([
            'jumlah_pesanan' => ['required', 'integer', 'min:1', 'max:'.$varian->stok],
        ], [
            'jumlah_pesanan.max' => 'Stok '.$varian->nama.' tinggal '.$varian->stok.'.',
        ]);

        // validasi jika belum login
        if (! Auth::user()) {
            return redirect()->route('login');
        }

        $total_harga = $this->jumlah_pesanan * $varian->harga;

        // mengecek data pesanan
        $pesanan = pesanan::where('id_user', Auth::user()->id)->where('status', 0)->first();
        if (empty($pesanan)) {
            pesanan::create([
                'id_user' => Auth::user()->id,
                'total_harga' => $total_harga,
                'status' => 0,
            ]);

            $pesanan = pesanan::where('id_user', Auth::user()->id)->where('status', 0)->first();
            $pesanan->kode_pemesanan = 'SP-'.$pesanan->id;
            $pesanan->update();
        } else {
            $pesanan->total_harga = $pesanan->total_harga + $total_harga;
            $pesanan->update();
        }

        // menyimpan pesanan detail
        pesanandetail::create([
            'id_varian' => $varian->id,
            'id_pesanan' => $pesanan->id,
            'jumlah_pesanan' => $this->jumlah_pesanan,
            'total_harga' => $total_harga,
        ]);

        $varian->decrement('stok', $this->jumlah_pesanan);

        $this->dispatch('masukKeranjang');

        session()->flash('message', 'Semprong masuk ke tas belanja.');

        return redirect()->back();
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
