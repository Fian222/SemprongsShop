<?php

namespace App\Livewire;

use App\Exceptions\KeranjangDitolak;
use App\Models\Keranjang;
use App\Models\pesanan;
use App\Models\pesanandetail;
use App\Models\User;
use App\Models\Varian;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

// Checkout sementara: isi keranjang menjadi pesanan lama (status 1, bayar transfer) sampai checkout Midtrans #15
class Checkout extends Component
{
    public $total_harga;

    public $nohp;

    public $alamat;

    public function mount()
    {
        if (! Auth::user()) {
            return redirect()->guest(route('login'));
        }

        $keranjang = Keranjang::milikPengunjung();
        if (! $keranjang || $keranjang->items->isEmpty()) {
            return redirect()->route('keranjang');
        }

        $this->nohp = Auth::user()->notelp;
        $this->alamat = Auth::user()->alamat;
        $this->total_harga = $keranjang->subtotal();
    }

    public function checkout()
    {
        // user sering menulis nomor dengan spasi/tanda hubung, mis. 0812-3456-7890
        $this->nohp = preg_replace('/[\s\-]/', '', (string) $this->nohp);

        $this->validate([
            'nohp' => ['required', 'regex:/^(\+62|62|0)8[1-9][0-9]{7,10}$/'],
            'alamat' => 'required',
        ], [
            'nohp.regex' => 'No. HP harus diawali 08 / 62 / +62 dan terdiri dari 10–13 digit.',
        ]);

        $keranjang = Keranjang::milikPengunjung();
        if (! $keranjang || $keranjang->items->isEmpty()) {
            return redirect()->route('keranjang');
        }

        try {
            $this->buatPesanan($keranjang);
        } catch (KeranjangDitolak $e) {
            return $this->addError('keranjang', $e->getMessage());
        }

        // simpan nomor hp dan alamat ke user
        $user = User::where('id', Auth::user()->id)->first();
        $user->notelp = $this->nohp;
        $user->alamat = $this->alamat;
        $user->update();

        $this->dispatch('keranjang-diperbarui');

        session()->flash('message', 'Pesanan dibuat. Silakan transfer sesuai total.');

        return redirect()->route('history');
    }

    // Stok dipesan di sini, bukan saat masuk keranjang. Baris varian dikunci supaya dua checkout tidak berebut stok yang sama.
    protected function buatPesanan(Keranjang $keranjang): pesanan
    {
        return DB::transaction(function () use ($keranjang) {
            $items = $keranjang->items()->lockForUpdate()->get();
            $varians = Varian::whereIn('id', $items->pluck('varian_id'))->lockForUpdate()->get()->keyBy('id');

            foreach ($items as $item) {
                $varians[$item->varian_id]->pastikanBisaDipesan($item->jumlah);
            }

            $pesanan = pesanan::create([
                'id_user' => Auth::user()->id,
                'total_harga' => $items->sum(fn ($item) => $varians[$item->varian_id]->harga * $item->jumlah),
            ]);
            $pesanan->status = 1;
            $pesanan->kode_pemesanan = 'SP-'.$pesanan->id;
            $pesanan->save();

            foreach ($items as $item) {
                $varian = $varians[$item->varian_id];
                pesanandetail::create([
                    'id_varian' => $varian->id,
                    'id_pesanan' => $pesanan->id,
                    'jumlah_pesanan' => $item->jumlah,
                    'total_harga' => $varian->harga * $item->jumlah,
                ]);
                $varian->decrement('stok', $item->jumlah);
            }

            $keranjang->items()->delete();

            return $pesanan;
        });
    }

    public function render()
    {
        return view('livewire.checkout')
            ->extends('layouts.app')
            ->section('content');
    }
}
