<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Varian;
use App\Support\EksporPesanan;
use App\Support\RingkasanPenjualan;
use Illuminate\Support\Carbon;
use Livewire\Component;

class Dashboard extends Component
{
    // periode omzet, grafik, terlaris, dan rentang ekspor bawaan
    private const HARI = 30;

    // rentang tanggal dibuat untuk ekspor CSV pesanan
    public $dari = '';

    public $sampai = '';

    public function mount()
    {
        $this->dari = $this->awalPeriode()->toDateString();
        $this->sampai = today()->toDateString();
    }

    // rentang diperiksa di sini supaya galatnya tampil di form; unduhannya dilayani EksporPesananController
    public function ekspor()
    {
        $rentang = $this->validate(...EksporPesanan::aturan());

        return $this->redirectRoute('admin.pesanan.ekspor', $rentang);
    }

    public function render(RingkasanPenjualan $ringkasan)
    {
        return view('livewire.dashboard', [
            'omzetHariIni' => $ringkasan->omzet(today()),
            'omzet30Hari' => $ringkasan->omzet($this->awalPeriode()),
            'omzetHarian' => $ringkasan->omzetHarian(self::HARI),
            'terlaris' => $ringkasan->terlaris($this->awalPeriode()),
            'jumlahPerStatus' => $ringkasan->jumlahPerStatus(),
            'jumlahPelanggan' => User::count(),
            'stokMenipis' => Varian::with('produk')->where('aktif', true)->stokMenipis()->orderBy('stok')->orderBy('id')->get(),
        ])
            ->extends('layouts.adminapp')
            ->section('content');
    }

    private function awalPeriode(): Carbon
    {
        return today()->subDays(self::HARI - 1);
    }
}
