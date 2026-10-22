<?php

namespace App\Livewire;

use App\Models\barang;
use App\Models\pesanan;
use App\Models\pesanandetail;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ProdukDetail extends Component
{
    public $product, $jumlah_pesanan, $pesanann;

    public function mount($id)
    {

        $barangs = barang::find($id);

        if ($barangs) {
            $this->product = $barangs;
        }
    }

    public function masukkankeranjang()
    {
        $this->validate([
            'jumlah_pesanan' => 'required'
        ]);

        //validasi jika belum login
        if (!Auth::user()) {
            return redirect()->route('login');
        }

        $total_harga = $this->jumlah_pesanan * $this->product->harga_barang;

        // mengecek data pesanan
        $pesanan = pesanan::where('id_user', Auth::user()->id)->where('status', 0)->first();
        if (empty($pesanan)) {
            pesanan::create([
                'id_user' => Auth::user()->id,
                'total_harga' => $total_harga,
                'status' => 0,
            ]);

            $pesanan = pesanan::where('id_user', Auth::user()->id)->where('status', 0)->first();
            $pesanan->kode_pemesanan = 'SP-' . $pesanan->id;
            $pesanan->update();
        } else {
            $pesanan->total_harga = $pesanan->total_harga + $total_harga;
            $pesanan->update();
        }

        //menyimpan pesanan detail
        pesanandetail::create([
            'id_barang' => $this->product->id,
            'id_pesanan' => $pesanan->id,
            'jumlah_pesanan' => $this->jumlah_pesanan,
            'total_harga' => $total_harga,
        ]);

        $pesanandetail = pesanandetail::where('id_pesanan', $pesanan->id)->get();
        foreach ($pesanandetail as $key => $value) {
            $barang = barang::where('id', $value->id_barang)->first();
            $pdetail = array(
                'stok_barang' => $barang->stok_barang - $value->jumlah_pesanan,
            );

            $barang = barang::where('id', $value->id_barang)->update($pdetail);
        }

        $this->dispatch('masukKeranjang');

        session()->flash('message', 'Sukses Masuk Pesanan');

        return redirect()->back();
    }

    public function render()
    {
        return view('livewire.produk-detail')
            ->extends('layouts.app')
            ->section('content');
    }
}
